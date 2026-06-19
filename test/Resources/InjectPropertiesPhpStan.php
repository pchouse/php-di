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

namespace PChouse\Resources;

use PChouse\Di\Inject;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;

class InjectPropertiesPhpStan implements ReadWritePropertiesExtension
{

    public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
    {
        return false;
    }

    /**
     * @throws \ReflectionException
     */
    public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
    {
        return $this->hasAttributeIjenct($property, $propertyName);
    }

    /**
     * @throws \ReflectionException
     */
    public function isInitialized(PropertyReflection $property, string $propertyName): bool
    {
        return $this->hasAttributeIjenct($property, $propertyName);
    }

    /**
     * @param \PHPStan\Reflection\PropertyReflection $property
     * @param string $propertyName
     *
     * @return bool
     * @throws \ReflectionException
     */
    protected function hasAttributeIjenct(PropertyReflection $property, string $propertyName): bool
    {
        return \count(
            (new \ReflectionClass($property->getDeclaringClass()->getName()))
                    ->getProperty($propertyName)
                    ->getAttributes(Inject::class)
        ) > 0;
    }
}
