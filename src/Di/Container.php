<?php
/*
 * Copyright (c) 2023. Reflexão, Sistemas e Estudos Informáticos, lda
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE
 *
 *
 */

declare(strict_types=1);

namespace PChouse\Di;

/**
 * Dependency injection container
 */
class Container
{

    /**
     * The default binds file path
     *
     * @var string|null
     */
    public static ?string $bindsFilePath = null;

    /**
     * The default routes file path
     *
     * @var string|null
     */
    public static ?string $routesFilePath = null;

    protected \Logger $logger;

    /**
     * The Container instance
     *
     * @var \PChouse\Di\Container|null
     */
    protected static Container|null $selfInstance = null;

    /**
     * The binds stack
     *
     * @var \PChouse\Di\IBind[]
     */
    protected array $bindStack = [];

    /**
     * Singletons stack
     *
     * @var object[]
     */
    protected array $singletons = [];

    /**
     * @throws \PChouse\Di\DiException
     */
    protected function __construct()
    {
        $this->logger = \Logger::getLogger(static::class);
        $this->logger->debug("New Instance");
        $this->initBinds();
        $this->initRoutes();
    }

    /**
     * Init container
     *
     * @return void
     * @throws \PChouse\Di\DiException
     */
    protected function initBinds(): void
    {
        $this->logger->debug("Init binds in DI Container");
        if (static::$bindsFilePath === null) {
            return;
        }
        if (!\is_file(static::$bindsFilePath) || !\is_readable(static::$bindsFilePath)) {
            return;
        }
        $binds = require static::$bindsFilePath;
        foreach ($binds as $bind) {
            $this->putBindInStack($bind);
        }
    }

    /**
     * Init routes container
     *
     * @throws \PChouse\Di\DiException
     */
    protected function initRoutes(): void
    {
        $this->logger->debug("Init routes in DI Container");
        if (static::$routesFilePath === null) {
            return;
        }
        if (!\is_file(static::$routesFilePath) || !\is_readable(static::$routesFilePath)) {
            return;
        }
        $routes = require static::$routesFilePath;
        foreach ($routes as $route) {
            $this->putBindInStack($route);
        }
    }

    /**
     *
     * Build container
     *
     * @param \PChouse\Di\IBind[] $binds
     *
     * @return void
     * @throws \PChouse\Di\DiException
     */
    public static function build(array $binds): void
    {
        if (self::$selfInstance === null || \count($binds) > 0) {
            self::$selfInstance = new self();
        }
        self::$selfInstance->logger->debug("Build DI Container start");
        foreach ($binds as $bind) {
            static::$selfInstance?->putBindInStack($bind);
        }
    }

    /**
     *
     * Build container
     *
     * @return void
     */
    public static function reset(): void
    {
        if (self::$selfInstance === null) {
            return;
        }
        self::$selfInstance->logger->debug("Build DI Container reset");
        self::$selfInstance = null;
    }

    /**
     *
     * Get controller for route
     *
     * @template T of object
     * @param class-string<T> $name
     *
     * @return T
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public static function getRoute(string $name)
    {
        if (!isset(static::$selfInstance)) {
            static::build([]);
        }

        /** @var \PChouse\Di\Container $container */
        $container = static::$selfInstance;

        $container->logger->debug("Getting Route controller for $name");

        $bind = $container->getBindFromStack($name);

        if ($bind->getScope() !== Scope::ROUTE) {
            $msg = "'$name' is not a DI route";
            $container->logger->debug($msg);
            throw new DiException($msg, DI_BIND_IS_NOT_ROUTE);
        }
        /** @phpstan-ignore-next-line */
        return $container->createInstance($bind);
    }


    /**
     * @template T
     *
     * @param class-string<T>|string $bindName
     *
     * @return T | mixed
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public static function get(string $bindName): mixed //@phpstan-ignore-line
    {
        /**  */
        if (!isset(static::$selfInstance)) {
            static::build([]);
        }

        /** @var \PChouse\Di\Container $container */
        $container = static::$selfInstance;

        $container->logger->debug("Get instance for $bindName");

        $key  = Container::keyBuilder($bindName);
        $bind = $container->getBindFromStack($bindName);

        if (\in_array($bind->getScope(), [Scope::MOCK, Scope::PROVIDES])) {
            $value = $bind->getValue();
            return \is_callable($value) ? \call_user_func($value) : $value;
        }

        if ($bind->getScope() === Scope::ROUTE) {
            throw new DiException(
                "Router cannot be getter from this method",
                DI_GET_ROUTE_FROM_FROM_WRONG_GETTER
            );
        }

        if ($bind->getScope() === Scope::SINGLETON) {
            if (!\array_key_exists($key, $container->singletons)) {
                if (\is_callable($bind->getValue())) {
                    $container->singletons[$key] = \call_user_func($bind->getValue());
                } else {
                    $container->singletons[$key] = $container->createInstance($bind);
                    $container->logger->info("Singleton $bindName added to Singleton stack");
                }
            }
            $container->logger->info("Return $bindName from Singleton stack");
            return $container->singletons[$key];
        }

        $container->logger->info("Going to create instance $bindName to return");

        if (\is_callable($bind->getValue())) {
            return \call_user_func($bind->getValue());
        }

        return $container->createInstance($bind);
    }

    /**
     * @param \PChouse\Di\IBind $bind
     *
     * @return object
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    protected function createInstance(IBind $bind): object
    {
        try {
            $reflectionClass = new \ReflectionClass($bind->getValue());
            $hasEvents       = $reflectionClass->isSubclassOf(IDIEvents::class);

            $constructorArgs = [];

            $constructorParameters = $reflectionClass->getConstructor()?->getParameters() ?? [];
            foreach ($constructorParameters as $parameter) {
                /** @phpstan-ignore-next-line */
                $constructorArgs[] = Container::get($parameter->getType()->getName());
            }

            /** @var \PChouse\Di\IDIEvents $instance */
            $instance = $reflectionClass->newInstanceArgs([...$constructorArgs]);

            if ($hasEvents) {
                $instance->afterInstanceCreated();
            }

            $classProperties = $reflectionClass->getProperties();

            foreach ($classProperties as $property) {
                $countAttributes = \count($property->getAttributes(Inject::class));

                if ($countAttributes === 0) {
                    continue;
                }

                /** @noinspection all */
                $property->setAccessible(true);

                $property->setValue(
                    $instance,
                    /** @phpstan-ignore-next-line */
                    Container::get($property->getType()->getName() ?? "")
                );
            }

            if ($hasEvents) {
                $instance->beforeReturnInstance();
            }

            return $instance;
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw $e;
        }
    }

    /**
     * Generate the bind stack key
     *
     * @param string $name The bind name
     *
     * @return string|class-string
     */
    protected static function keyBuilder(string $name): string
    {
        return \md5(\trim($name, " \t\n\r\0\x0B\\"));
    }

    /**
     * Put bind in stack
     *
     * @throws \PChouse\Di\DiException
     */
    protected function putBindInStack(IBind $bind): void
    {
        $key = $this->keyBuilder($bind->getBinds());

        if ($bind->getScope() === Scope::MOCK) {
            if (!defined("PHP_DI_TEST")) {
                throw new DiException(
                    "MOCK binds only allow in tests. 
                    If you running test please in your test bootstrap.php 
                    file define the constant PHP_DI_TEST (with any value)",
                    DI_SET_MOCK_BIND_IN_NON_TEST
                );
            }
            $this->bindStack[$key] = $bind;
            return;
        }

        if (\array_key_exists($key, $this->bindStack)) {
            throw new DiException(
                \sprintf(
                    "A bind for '%s' already exists in container",
                    $bind->getBinds()
                )
            );
        }

        $this->bindStack[$key] = $bind;
    }

    /**
     * Get bind from bind stack
     *
     * @param class-string|string $name
     *
     * @return \PChouse\Di\IBind
     * @throws \PChouse\Di\DiException
     */
    protected function getBindFromStack(string $name): IBind
    {
        $key = static::keyBuilder($name);
        if (\array_key_exists($key, $this->bindStack)) {
            return $this->bindStack[$key];
        }
        throw new DiException(
            \sprintf("Cannot get bind '%s' because not exists in stack", $name),
            DI_BIND_NOT_EXIST
        );
    }
}
