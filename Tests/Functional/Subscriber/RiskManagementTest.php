<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementInterface;
use SwagPaymentPayPalUnified\Subscriber\RiskManagement;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class RiskManagementTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testOnCheckProductCategoryFrom()
    {
        $eventArgs = $this->getEventArgs();

        static::assertNull($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckProductCategoryFromProductIsNotInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 212);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 6);

        static::assertNull($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckProductCategoryFromProductIsInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        $this->setRequestParameterToFront();

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 6);

        static::assertTrue($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function testOnCheckProductCategoryFromCategoryIsNotAmongTheParents()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 7);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 3);

        static::assertNull($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertNull($eventArgs->getReturn());

        $templateResult = Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts');
        static::assertNotEmpty($templateResult);
    }

    public function testOnCheckProductCategoryFromCategoryIsAmongTheParents()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_in_category.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 3);

        static::assertTrue($this->getSubscriber()->onCheckProductCategoryFrom($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function testOnCheckProductCategoryFromIsNotInAcceptedList()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        $this->setRequestParameterToFront('frontend', 'listing', 'notAcceptedAction');

        static::assertNull($this->getSubscriber()->onCheckProductCategoryFrom($this->getEventArgs()));
    }

    public function testOnCheckProductCategoryFromIsInAcceptedList()
    {
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        $this->setRequestParameterToFront('frontend', 'listing');

        static::assertTrue($this->getSubscriber()->onCheckProductCategoryFrom($this->getEventArgs()));
    }

    public function testOnCheckRiskAttribIsNot()
    {
        $eventArgs = $this->getEventArgs();
        Shopware()->Container()->reset('front');

        $this->setRequestParameterToFront('frontend', 'notAcceptedController');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIsNot($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckRiskAttribIsNotProductAttributeMatched()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIsNot($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckRiskAttribIsNotProductAttributeMatchedInCategory()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIsNot($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckRiskAttribIs()
    {
        $eventArgs = $this->getEventArgs();

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckRiskAttribIsProductReturnshouldBeTrue()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertTrue($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertTrue($eventArgs->getReturn());
    }

    public function testOnCheckRiskAttribIsCategoryReturnshouldBeNullTemplateShouldContain()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetUnset(RiskManagementInterface::PRODUCT_ID_SESSION_NAME);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, 6);
        $this->setRequestParameterToFront('frontend', 'listing');
        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'attr1|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());

        $result = Shopware()->Container()->get('template')->getTemplateVars('riskManagementMatchedProducts');

        static::assertSame('["SW10178"]', $result);
    }

    public function testOnCheckRiskAttribIsInvalidProductAttributeMatched()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_invalid_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', 'invalidAttr|2');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testOnCheckRiskAttribIsInvalidEmptyProductAttributeMatched()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_invalid_empty_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('value', '');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    public function testShouldContinueCheckWithInvalidPaymentId()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/risk_management_rules_product_attr_is.sql');
        static::assertTrue(\is_string($sql));
        Shopware()->Container()->get('dbal_connection')->exec($sql);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::CATEGORY_ID_SESSION_NAME, null);
        Shopware()->Container()->get('session')->offsetSet(RiskManagementInterface::PRODUCT_ID_SESSION_NAME, 178);
        $this->setRequestParameterToFront('frontend', 'listing');

        $eventArgs = $this->getEventArgs();
        $eventArgs->set('paymentID', 112);
        $eventArgs->set('value', '');

        static::assertNull($this->getSubscriber()->onCheckRiskAttribIs($eventArgs));
        static::assertNull($eventArgs->getReturn());
    }

    /**
     * @return RiskManagement
     */
    private function getSubscriber()
    {
        return new RiskManagement(
            Shopware()->Container()->get('paypal_unified.risk_management_helper'),
            Shopware()->Container()->get('template'),
            Shopware()->Container()->get('paypal_unified.dependency_provider'),
            Shopware()->Container()->get('paypal_unified.payment_method_provider')
        );
    }

    /**
     * @return \Enlight_Event_EventArgs
     */
    private function getEventArgs()
    {
        $eventArgs = new \Enlight_Event_EventArgs();
        $paymentMethodProvider = Shopware()->Container()->get('paypal_unified.payment_method_provider');
        $eventArgs->set('paymentID', $paymentMethodProvider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));

        return $eventArgs;
    }

    /**
     * @param string $module
     * @param string $controller
     * @param string $action
     */
    private function setRequestParameterToFront($module = 'frontend', $controller = 'listing', $action = 'index')
    {
        $request = new \Enlight_Controller_Request_RequestHttp();
        $request->setActionName($action);
        $request->setControllerName($controller);
        $request->setModuleName($module);
        Shopware()->Container()->get('front')->setRequest($request);
    }
}
