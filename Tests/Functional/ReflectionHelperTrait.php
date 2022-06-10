<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

trait ReflectionHelperTrait
{
    /**
     * @param class-string $className
     * @param string       $methodName
     *
     * @return ReflectionMethod
     */
    public function getReflectionMethod($className, $methodName)
    {
        $method = (new ReflectionClass($className))->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param class-string $className
     * @param string       $methodName
     *
     * @return ReflectionProperty
     */
    public function getReflectionProperty($className, $methodName)
    {
        $property = (new ReflectionClass($className))->getProperty($methodName);
        $property->setAccessible(true);

        return $property;
    }
}
