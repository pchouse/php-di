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

use PChouse\Di\Bind;
use PChouse\Di\Scope;
use PChouse\Resources\Dependency;
use PChouse\Resources\DependencyProvides;
use PChouse\Resources\DependencyProvidesSingleton;
use PChouse\Resources\DependencyWiredOne;
use PChouse\Resources\DependencyWiredSingleton;
use PChouse\Resources\DependencyWiredTwo;
use PChouse\Resources\IDependency;
use PChouse\Resources\IDependencyWiredOne;
use PChouse\Resources\IDependencyWiredSingleton;
use PChouse\Resources\IDependencyWiredTwo;
use Random\Randomizer;

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
