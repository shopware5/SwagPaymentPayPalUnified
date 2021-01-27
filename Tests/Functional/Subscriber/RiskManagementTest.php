<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementInterface;
use SwagPaymentPayPalUnified\Subscriber\RiskManagement;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class RiskManagementTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function test_onCheckProductCategoryFrom()
    {
        $eventArgs = $this->getEventArgs();

        static::assertTrue($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function test_onCheckProductCategoryFrom_productIsNotInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 212);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 6);

        static::assertNull($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function test_onCheckProductCategoryFrom_productIsInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 6);

        static::assertTrue($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function test_onCheckProductCategoryFrom_categoryIsNotAmongTheParents()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 7);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 3);

        $templateResult = Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts');

        static::assertNull($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertNull($eventArgs->getReturn());
        static::assertNotEmpty($templateResult);
    }

    public function test_onCheckProductCategoryFrom_categoryIsAmongTheParents()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 3);

        static::assertTrue($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function test_onCheckRiskAttribIsNot()
    {
        $eventArgs = $this->getEventArgs();

        static::assertTrue($this->getSubscriber()->onCheckRiskAttribIsNot($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function test_onCheckRiskAttribIsNot_productAttributeMatched()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIsNot($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function test_onCheckRiskAttribIsNot_productAttributeMatchedInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIsNot($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function test_onCheckRiskAttribIs()
    {
        $eventArgs = $this->getEventArgs();

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function test_onCheckRiskAttribIs_product_returnshouldBeTrue()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertTrue($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function test_onCheckRiskAttribIs_category_returnshouldBeNull_templateShouldContain()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetUnset(RiskManagementInterface::PRODUCT_ID_SESSION_NAME);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());

        $result = Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts');

        static::assertSame('["SW10178"]', $result);
    }

    /**
     * @return RiskManagement
     */
    private function getSubscriber()
    {
        return new RiskManagement(
            Shopware()->Container()->get('paypal_unified.risk_management_helper'),
            Shopware()->Container()->get('template')
        );
    }

    /**
     * @return \Enlight_Event_EventArgs
     */
    private function getEventArgs()
    {
        return new \Enlight_Event_EventArgs();
    }
}
