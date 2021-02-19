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
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementHelper;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementInterface;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class RiskManagementHelperTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testCreateAttribute()
    {
        $helper = $this->getHelper();
        $attribute = $helper->createAttribute('attr1|FooBar');

        static::assertInstanceOf(Attribute::class, $attribute);
        static::assertSame('attr1', $attribute->getAttributeName());
        static::assertSame('FooBar', $attribute->getAttributeValue());
    }

    public function testCreateContext()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $helper = $this->getHelper();
        $attribute = $helper->createAttribute('attr1|FooBar');
        $context = $helper->createContext($attribute, 111);

        static::assertInstanceOf(Context::class, $context);
        static::assertInstanceOf(Attribute::class, $context->getAttribute());
        static::assertSame(111, $context->getEventCategoryId());
        static::assertSame(178, $context->getSessionProductId());
        static::assertSame(6, $context->getSessionCategoryId());
    }

    public function testIsProductInCategoryShouldBeTrue()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 6);

        $result = $helper->isProductInCategory($context);

        static::assertTrue($result);
    }

    public function testIsProductInCategoryShouldBeFalse()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 2);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 6);

        $result = $helper->isProductInCategory($context);

        static::assertfalse($result);
    }

    public function testIsCategoryAmongTheParentsShouldBeTrue()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 3);

        $result = $helper->isCategoryAmongTheParents($context);

        static::assertTrue($result);
    }

    public function testIsCategoryAmongTheParentsShouldBeFalse()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 12);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 6);

        $result = $helper->isCategoryAmongTheParents($context);

        static::assertfalse($result);
    }

    public function testHasProductAttributeValueShouldBeTrue()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute(['attr1', '2']), 6);

        $result = $helper->hasProductAttributeValue($context);

        static::assertTrue($result);
    }

    public function testHasProductAttributeValueShouldBeFalse()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute(['attr1', '2']), 6);

        $result = $helper->hasProductAttributeValue($context);

        static::assertFalse($result);
    }

    public function testGetProductOrdernumbersMatchedAttribute()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute(['attr1', '2']), 6);

        $result = $helper->getProductOrdernumbersMatchedAttribute($context);

        static::assertCount(1, $result);
        static::assertSame('SW10178', $result[0]);
    }

    public function testGetProductIOrdernumbersNotMatchedAttribute()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $sql = 'UPDATE s_articles_attributes SET attr1 = 2';
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $sql = 'UPDATE s_articles_attributes SET attr1 = 1 WHERE id = 429;';
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute(['attr1', '2']), 6);

        $result = $helper->getProductOrdernumbersNotMatchedAttribute($context);

        static::assertCount(1, $result);
        static::assertSame('SW10178', $result[0]);
    }

    public function testHasProductAttributeValueWithInvalidAttribute()
    {
        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 1);

        $result = $helper->hasProductAttributeValue($context);

        static::assertFalse($result);
    }

    public function testGetProductOrdernumbersMatchedAttributeWithInvalidAttribute()
    {
        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 1);

        $result = $helper->getProductOrdernumbersMatchedAttribute($context);

        static::assertEmpty($result);
    }

    public function testGetProductOrdernumbersNotMatchedAttributeWithInvalidAttribute()
    {
        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 1);

        $result = $helper->getProductOrdernumbersNotMatchedAttribute($context);

        static::assertEmpty($result);
    }

    public function testGetProductOrderNumbersInCategoryShouldReturnAEmptyArray()
    {
        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 99);

        $result = $helper->getProductOrderNumbersInCategory($context);

        static::assertEmpty($result);
    }

    public function testGetProductOrderNumbersInCategoryShouldReturnANotEmptyArray()
    {
        $helper = $this->getHelper();
        $context = $helper->createContext(new Attribute([]), 6);

        $result = $helper->getProductOrderNumbersInCategory($context);

        static::assertNotEmpty($result);
    }

    /**
     * @return RiskManagementHelper
     */
    private function getHelper()
    {
        return new RiskManagementHelper(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.dependency_provider')
        );
    }
}
