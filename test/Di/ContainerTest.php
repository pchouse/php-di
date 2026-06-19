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

use PChouse\Resources\DependencyController;
use PChouse\Resources\DependencyProvides;
use PChouse\Resources\DependencyProvidesSingleton;
use PChouse\Resources\IDependency;
use PChouse\Resources\IDependencyController;
use PChouse\Resources\IDependencyWiredOne;
use PChouse\Resources\IDependencyWiredSingleton;
use PChouse\Resources\IDependencyWiredTwo;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;

class ContainerTest extends TestCase
{
    /**
     * @before
     * @return void
     */
    public function before(): void
    {
        Container::reset();
        Container::$bindsFilePath  = DI_TEST_BINDS;
        Container::$routesFilePath = DI_TEST_ROUTES;
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testNoBindException(): void
    {
        $this->expectException(DiException::class);
        $this->expectExceptionCode(DI_BIND_NOT_EXIST);
        Container::get("AnyClass");
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testSingleton(): void
    {
        $singleton = Container::get(IDependencyWiredSingleton::class);
        $random    = (new Randomizer())->getInt(9, 999);
        $singleton->setRandom($random);

        $this->assertSame(
            $random,
            Container::get(IDependencyWiredSingleton::class)->getRandom()
        );
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testProvides()
    {
        $provides = Container::get(DependencyProvides::class);
        $random   = $provides->getRandom();

        $this->assertGreaterThanOrEqual(9, $random);

        $this->assertLessThan(99999, $random);

        $this->assertNotEquals(
            $random,
            Container::get(DependencyProvides::class)->getRandom()
        );
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testProvidesSingleton()
    {
        $provides = Container::get(DependencyProvidesSingleton::class);
        $random   = $provides->getRandom();

        $this->assertGreaterThanOrEqual(9, $random);

        $this->assertLessThan(99999, $random);

        $this->assertSame(
            $random,
            Container::get(DependencyProvidesSingleton::class)->getRandom()
        );
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testTransient(): void
    {
        $dependency = Container::get(IDependency::class);

        $this->assertTrue($dependency->isAfterInstanceCreatedInit());
        $this->assertTrue($dependency->isBeforeReturnInstanceInit());

        $dependencyWiredOne = $dependency->getDependencyWiredOne();
        $dependencyWiredTwo = $dependency->getDependencyWiredTwo();

        $this->assertInstanceOf(
            IDependencyWiredOne::class,
            $dependencyWiredOne
        );

        $this->assertInstanceOf(
            IDependencyWiredTwo::class,
            $dependencyWiredTwo
        );

        $this->assertTrue($dependencyWiredOne->isAfterInstanceCreatedInit());
        $this->assertTrue($dependencyWiredOne->isBeforeReturnInstanceInit());

        $this->assertTrue($dependencyWiredTwo->isAfterInstanceCreatedInit());
        $this->assertTrue($dependencyWiredTwo->isBeforeReturnInstanceInit());

        $rand = (new Randomizer())->getInt(9, 999);

        $dependencyWiredOne->getDependencyWiredSingleton()->setRandom($rand);

        $this->assertSame($rand, $dependencyWiredTwo->getDependencyWiredSingleton()->getRandom());
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testRouteFromWrongContainer(): void
    {
        $this->expectException(DiException::class);
        $this->expectExceptionCode(DI_GET_ROUTE_FROM_FROM_WRONG_GETTER);
        Container::get(IDependencyController::class);
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testRouteContainer(): void
    {
        $this->assertInstanceOf(
            DependencyController::class,
            Container::getRoute(IDependencyController::class)
        );
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testFromWrongContainer(): void
    {
        $this->expectException(DiException::class);
        $this->expectExceptionCode(DI_BIND_IS_NOT_ROUTE);
        Container::getRoute(IDependency::class);
    }

    /**
     * @test
     * @return void
     * @throws \PChouse\Di\DiException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testMock()
    {
        $rand = -999;
        $mock = $this->createMock(IDependencyWiredSingleton::class);
        $mock->method("getRandom")->willReturn($rand);

        Container::build(
            [
                new Bind(Scope::MOCK, IDependencyWiredSingleton::class, $mock)
            ]
        );

        $dependency = Container::get(IDependency::class);

        $this->assertSame(
            $rand,
            $dependency->getDependencyWiredOne()->getDependencyWiredSingleton()->getRandom()
        );
    }
}
