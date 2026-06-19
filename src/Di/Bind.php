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

class Bind implements IBind
{
    /**
     * @param \PChouse\Di\Scope $scope   The scope
     * @param class-string|string $binds The instance name or value name
     * @param mixed $value               The class name to instantiate or teh value to store
     */
    public function __construct(
        protected Scope  $scope,
        protected string $binds,
        protected mixed  $value
    ) {
    }

    /**
     * The instance name or value name
     *
     * @return string
     */
    public function getBinds(): string
    {
        return $this->binds;
    }

    /**
     * The scope
     *
     * @return \PChouse\Di\Scope
     */
    public function getScope(): Scope
    {
        return $this->scope;
    }

    /**
     * The class name to instantiate or teh value to store
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
