<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Tests\Functional\Controller\Widgets\_mocks\PaypalUnifiedExpressCheckoutControllerMock;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ResetSessionTrait;

class PaypalUnifiedExpressCheckoutTest extends TestCase
{
    use ResetSessionTrait;
    use DatabaseTestCaseTrait;

    public function testCreatePaymentActionShouldNotAddPaymentDiscountToBasket()
    {
        $sql = \file_get_contents(__DIR__ . '/_fixtures/paypal_settings.sql');
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $sql = 'UPDATE s_core_paymentmeans SET debit_percent = -10 WHERE id = 5';
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $controller = $this->getController();
        $controller->Request()->setParam('addProduct', true);
        $controller->Request()->setParam('productNumber', 'SW10178');
        $controller->Request()->setParam('productQuantity', 1);

        $controller->createPaymentAction();

        $result = Shopware()->Modules()->Basket()->sGetBasket();

        Shopware()->Modules()->Basket()->sDeleteBasket();
        Shopware()->Container()->reset('front');
        $this->resetSession();

        $sql = 'DELETE FROM swag_payment_paypal_unified_settings_general WHERE id = 1';
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        $sql = 'UPDATE s_core_paymentmeans SET debit_percent = 0 WHERE id = 5';
        Shopware()->Container()->get('dbal_connection')->exec($sql);

        static::assertCount(1, $result['content']);
        static::assertSame('19,95', $result['Amount']);
    }

    /**
     * @return \Shopware_Controllers_Widgets_PaypalUnifiedExpressCheckout
     */
    private function getController()
    {
        $view = new \Enlight_View_Default(new \Enlight_Template_Manager());
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $response = new \Enlight_Controller_Response_ResponseTestCase();

        $controller = new PaypalUnifiedExpressCheckoutControllerMock();
        $controller->setContainer(Shopware()->Container());
        $controller->setView($view);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setFront(Shopware()->Container()->get('front'));
        $controller->Front()->setRequest($request);
        $controller->Front()->setResponse($response);

        $controller->init();

        $reflectionClass = new \ReflectionClass(\Shopware_Controllers_Widgets_PaypalUnifiedExpressCheckout::class);

        $paymentResourceReflectionProperty = $reflectionClass->getProperty('paymentResource');
        $clientReflectionProperty = $reflectionClass->getProperty('client');
        $dependencyProviderReflectionProperty = $reflectionClass->getProperty('dependencyProvider');
        $settingsServiceReflectionProperty = $reflectionClass->getProperty('settingsService');

        $paymentResourceReflectionProperty->setAccessible(true);
        $clientReflectionProperty->setAccessible(true);
        $dependencyProviderReflectionProperty->setAccessible(true);
        $settingsServiceReflectionProperty->setAccessible(true);

        $paymentResourceReflectionProperty->setValue($controller, Shopware()->Container()->get('paypal_unified.payment_resource'));
        $clientReflectionProperty->setValue($controller, Shopware()->Container()->get('paypal_unified.client_service'));
        $dependencyProviderReflectionProperty->setValue($controller, Shopware()->Container()->get('paypal_unified.dependency_provider'));
        $settingsServiceReflectionProperty->setValue($controller, Shopware()->Container()->get('paypal_unified.settings_service'));

        return $controller;
    }
}
