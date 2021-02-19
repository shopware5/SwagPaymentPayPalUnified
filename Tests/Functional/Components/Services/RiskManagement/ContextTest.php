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
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\Context;

class ContextTest extends TestCase
{
    public function testContextShouldBeNull()
    {
        $context = new Context(new Attribute([]));

        static::assertNull($context->getSessionProductId());
        static::assertNull($context->getSessionCategoryId());
        static::assertNull($context->getEventCategoryId());

        static::assertInstanceOf(Attribute::class, $context->getAttribute());
        static::assertTrue($context->sessionCategoryIdIsNull());
        static::assertTrue($context->sessionProductIdIsNull());
        static::assertTrue($context->eventCategoryIdIsNull());
        static::assertTrue($context->categoryIdsAreTheSame());
    }

    public function testContextShouldNotBeNull()
    {
        $context = new Context(new Attribute([]), 12, 14, 16);

        static::assertSame(12, $context->getSessionProductId());
        static::assertSame(14, $context->getSessionCategoryId());
        static::assertSame(16, $context->getEventCategoryId());

        static::assertInstanceOf(Attribute::class, $context->getAttribute());
        static::assertFalse($context->sessionCategoryIdIsNull());
        static::assertFalse($context->sessionProductIdIsNull());
        static::assertFalse($context->eventCategoryIdIsNull());
        static::assertFalse($context->categoryIdsAreTheSame());
    }

    public function testContextCategoryIdsShouldBeTheSame()
    {
        $context = new Context(new Attribute([]), 12, 14, 14);

        static::assertSame(12, $context->getSessionProductId());
        static::assertSame(14, $context->getSessionCategoryId());
        static::assertSame(14, $context->getEventCategoryId());

        static::assertInstanceOf(Attribute::class, $context->getAttribute());
        static::assertFalse($context->sessionCategoryIdIsNull());
        static::assertFalse($context->sessionProductIdIsNull());
        static::assertFalse($context->eventCategoryIdIsNull());

        static::assertTrue($context->categoryIdsAreTheSame());
    }
}
