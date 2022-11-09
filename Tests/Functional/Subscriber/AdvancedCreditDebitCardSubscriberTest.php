<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\ClientToken;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\ClientTokenResource;
use SwagPaymentPayPalUnified\Subscriber\AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class AdvancedCreditDebitCardSubscriberTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testAddAcdcCorrectTemplateAssigns()
    {
        $view = new ViewMock(new Enlight_Template_Manager());
        $view->assign('sPayment', ['name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME]);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('confirm');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $this->insertGeneralSettingsFromArray([
            'active' => 1,
        ]);

        $this->insertAdvancedCreditDebitCardSettingsFromArray([
            'active' => 1,
        ]);

        $subscriber = $this->getSubscriber();
        $subscriber->onCheckout($enlightEventArgs);

        static::assertTrue($view->getAssign('paypalUnifiedAdvancedCreditDebitCardActive'));
    }

    /**
     * @return AdvancedCreditDebitCard
     */
    private function getSubscriber()
    {
        $clientTokenResource = $this->createMock(ClientTokenResource::class);
        $clientTokenResource->expects(static::once())->method('generateToken')->willReturn(new ClientToken());

        return new AdvancedCreditDebitCard(
            $this->getContainer()->get('paypal_unified.payment_method_provider'),
            $this->getContainer()->get('paypal_unified.settings_service'),
            $clientTokenResource,
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('paypal_unified.europe_service'),
            $this->getContainer()->get('paypal_unified.button_locale_service')
        );
    }
}
