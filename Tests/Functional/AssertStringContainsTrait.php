<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

trait AssertStringContainsTrait
{
    /**
     * @param object $object
     * @param string $expected
     * @param string $haystack
     *
     * @return void
     */
    public static function assertStringContains($object, $expected, $haystack)
    {
        if (\method_exists($object, 'assertStringContainsString')) {
            static::assertStringContainsString($expected, $haystack);

            return;
        }

        static::assertContains($expected, $haystack);
    }
}
