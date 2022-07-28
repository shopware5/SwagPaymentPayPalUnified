<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderFactory;
use SwagPaymentPayPalUnified\Components\Services\PayUponInvoiceInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice\DepositBankDetails;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Resource\OrderResource;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PaypalUnifiedV2PayUponInvoiceShouldSavePaymentInstructionsTest extends PaypalPaymentControllerTestCase
{
    use ContainerTrait;
    use SettingsHelperTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testIndexActionShouldSavePaymentInstructionsTest()
    {
        $sOrderVariables = [
            'sUserData' => require __DIR__ . '/_fixtures/getUser_result.php',
            'sBasket' => require __DIR__ . '/_fixtures/getBasket_result.php',
        ];

        $this->getContainer()->get('session')->offsetSet('sOrderVariables', $sOrderVariables);
        $this->getContainer()->get('session')->offsetSet('sUserId', 1);

        $dependencyProvider = $this->getContainer()->get('paypal_unified.dependency_provider');
        $payPalOrderParameterFacade = $this->getContainer()->get('paypal_unified.paypal_order_parameter_facade');
        $paypalOrder = $this->createPayPalOrder();

        $orderResource = $this->createMock(OrderResource::class);
        $orderResource->method('get')->willReturn($paypalOrder);
        $orderResource->method('create')->willReturn($paypalOrder);

        $orderFactory = $this->createMock(OrderFactory::class);
        $orderFactory->method('createOrder')->willReturn($paypalOrder);

        $paymentInstructionService = $this->createMock(PayUponInvoiceInstructionService::class);
        $paymentInstructionService->expects(static::once())->method('createInstructions');

        $controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice::class,
            [
                PaypalPaymentControllerTestCase::SERVICE_DEPENDENCY_PROVIDER => $dependencyProvider,
                PaypalPaymentControllerTestCase::SERVICE_ORDER_RESOURCE => $orderResource,
                PaypalPaymentControllerTestCase::SERVICE_PAYMENT_INSTRUCTION_SERVICE => $paymentInstructionService,
                PaypalPaymentControllerTestCase::SERVICE_ORDER_PARAMETER_FACADE => $payPalOrderParameterFacade,
                PaypalPaymentControllerTestCase::SERVICE_ORDER_FACTORY => $orderFactory,
            ]
        );

        $controller->indexAction();
    }

    /**
     * @return Order
     */
    private function createPayPalOrder()
    {
        $capture = new Capture();
        $capture->setStatus(PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $amount = new Capture\Amount();
        $amount->setValue('99,99');

        $capture->setAmount($amount);

        $authorization = new Authorization();
        $authorization->setStatus(PaymentStatusV2::ORDER_CAPTURE_COMPLETED);

        $payments = new Payments();
        $payments->setAuthorizations([$authorization]);
        $payments->setCaptures([$capture]);

        $amount = new Amount();
        $amount->setValue('100');

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payments);
        $purchaseUnit->setAmount($amount);

        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

        $depositBankDetails = new DepositBankDetails();
        $depositBankDetails->setAccountHolderName('Max Mustermann');
        $depositBankDetails->setBankName('Muster Bank');
        $depositBankDetails->setBic('any BIC');
        $depositBankDetails->setIban('any IBAN');

        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setDepositBankDetails($depositBankDetails);

        $paymentSource = new PaymentSource();
        $paymentSource->setPayUponInvoice($payUponInvoice);

        $order = new Order();
        $order->setId('anyId');
        $order->setIntent(PaymentIntentV2::CAPTURE);
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setPaymentSource($paymentSource);

        return $order;
    }
}
