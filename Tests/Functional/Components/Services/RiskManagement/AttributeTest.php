<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\RiskManagement;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\Attribute;

class AttributeTest extends TestCase
{
    public function testAttributeShouldBeNull()
    {
        $attribute = new Attribute([]);

        static::assertNull($attribute->getAttributeName());
        static::assertNull($attribute->getAttributeValue());
    }

    public function testAttributeShouldNotBeNull()
    {
        $attribute = new Attribute(['foo', 'bar']);

        static::assertSame('foo', $attribute->getAttributeName());
        static::assertSame('bar', $attribute->getAttributeValue());
    }
}
