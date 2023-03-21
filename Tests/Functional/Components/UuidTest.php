<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Uuid;

class UuidTest extends TestCase
{
    /**
     * @return void
     */
    public function testGenerateUuid()
    {
        $result = Uuid::generateUuid();

        $pattern = '/^[0-9a-fA-F]{8}-([0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$/';
        if (!\method_exists($this, 'assertMatchesRegularExpression')) {
            static::assertTrue((bool) \preg_match($pattern, $result));

            return;
        }

        static::assertMatchesRegularExpression($pattern, $result);
    }
}
