# PChouse PHP-DI

---

##### Dependency injection library

#### Install

```bash
composer require pchouse/php-di
```

#### Usage



Crete a binds file that return an array  
Binds.php
```php
    return [

        new Bind(
            Scope::TRANSIENT,
            IDependency::class,
            Dependency::class
        ),
    
        new Bind(
            Scope::TRANSIENT,
            IDependencyWiredOne::class,
            DependencyWiredOne::class
        ),
    
        new Bind(
            Scope::TRANSIENT,
            IDependencyWiredTwo::class,
            DependencyWiredTwo::class
        ),
    
        new Bind(
            Scope::SINGLETON,
            IDependencyWiredSingleton::class,
            DependencyWiredSingleton::class
        ),
    
        new Bind(
            Scope::PROVIDES,
            DependencyProvides::class,
            function () {
                $provides = new DependencyProvides();
                $provides->setRandom(
                    (new Randomizer())->getInt(9, 99999)
                );
                return $provides;
            }
        ),
    
        new Bind(
            Scope::SINGLETON,
            DependencyProvidesSingleton::class,
            function () {
                $provides = new DependencyProvidesSingleton();
                $provides->setRandom(
                    (new Randomizer())->getInt(9, 99999)
                );
                return $provides;
            }
        ),
    ];
```

Create a Routes file that return an array  
Routes.php
```php    
    return [
        new Bind(Scope::ROUTE, IDependencyController::class, DependencyController::class),
    ];
```
In your bootstrap.php or other place configure the container
```php 

    
    Container::$bindsFilePath  = "/path/to/Binds.php";
    Container::$routesFilePath = "/path/to/Routes.php";
```

Example of a class using Injection  
Mark the constructor with #[Inject] if it has arguments  
Mark the properties to be injected with #[Inject]
If you desire to use Events implement the IDIEvents interface
```php 
    
    class Dependency implements IDIEvents, IDependency
    {
    
        private bool $afterInstanceCreatedInit = false;
    
        private bool $beforeReturnInstanceInit = false;
    
        #[Inject]
        private IDependencyWiredOne $dependencyWiredOne;
    
        private int $rand = 0;
    
        #[Inject]
        public function __construct(private IDependencyWiredTwo $dependencyWiredTwo)
        {
        }
        
        public function getDependencyWiredOne(): IDependencyWiredOne
        {
            return $this->dependencyWiredOne;
        }
        
        public function setDependencyWiredOne(IDependencyWiredOne $dependencyWiredOne): void
        {
            $this->dependencyWiredOne = $dependencyWiredOne;
        }
        
        public function getDependencyWiredTwo(): IDependencyWiredTwo
        {
            return $this->dependencyWiredTwo;
        }
        
        public function setDependencyWiredTwo(IDependencyWiredTwo $dependencyWiredTwo): void
        {
            $this->dependencyWiredTwo = $dependencyWiredTwo;
        }
    
        public function afterInstanceCreated(): void
        {
            $this->afterInstanceCreatedInit = true;
        }
    
        /**
         * @throws \Exception
         */
        public function beforeReturnInstance(): void
        {
            if (!$this->afterInstanceCreatedInit) {
                throw new \Exception(
                    "The AfterInstanceCreated did not run"
                );
            }
            $this->beforeReturnInstanceInit = true;
        }
        
        public function isAfterInstanceCreatedInit(): bool
        {
            return $this->afterInstanceCreatedInit;
        }
        
        public function isBeforeReturnInstanceInit(): bool
        {
            return $this->beforeReturnInstanceInit;
        }
        
        public function getRand(): int
        {
            return $this->rand;
        }
        
        public function setRand(int $rand):void
        {
            $this->rand = $rand;
        }
    }
```

If in any part of the code to get manually an instance  

```php 
    
    $instance = \PChouse\Di\Container::get(ICalssName);
    // OR
    $route = \PChouse\Di\Container::getRoute(IClassName);  
```

In test mode to build with mocks  
In the test bootstrap.php set a constant: const PHP_DI_TEST = 1;  
Only the instance marked with mock will be replaced in the container  

```php        
    
       
     Container::build(
            [
                new Bind(Scope::MOCK, IDependencyWiredSingleton::class, $mock)
            ]
     );

     $dependency = Container::get(IDependency::class);

```

Scopes
```php 
    
    Scope::TRANSIENT // Allways a new instance
    Scope::SINGLETON // Create a singleton instance
    Scope::PROVIDES  // For manually created instaces
```

License

Copyright 2023 Reflexão, Sistemas e Estudos Informáticos, Lda

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction,
including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
THE USE OR OTHER DEALINGS IN THE SOFTWARE.
--- 

##### Art made by a Joseon Chinnampo soul for smart people 🇰🇷

