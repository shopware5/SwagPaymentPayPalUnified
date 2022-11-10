<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice\DepositBankDetails;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class PayUponInvoiceInstructionServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;

    /**
     * @return void
     */
    public function testCreateInstructions()
    {
        $ordernumber = 'anyOrderNumber';
        $order = $this->createPayPalOrder();

        $instructionService = $this->getContainer()->get('paypal_unified.pay_upon_invoice_instruction_service');
        $instructionService->createInstructions($ordernumber, $order);

        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select('*')
            ->from('swag_payment_paypal_unified_payment_instruction')
            ->where('order_number = :ordernumber')
            ->setParameter('ordernumber', $ordernumber)
            ->execute()
            ->fetch();

        static::assertSame('anyOrderNumber', $result['order_number']);
        static::assertSame('Muster Bank', $result['bank_name']);
        static::assertSame('Max Mustermann', $result['account_holder']);
        static::assertSame('any IBAN', $result['iban']);
        static::assertSame('any BIC', $result['bic']);
        static::assertSame('99,99', $result['amount']);
        static::assertSame('ABC123', $result['reference']);
        static::assertSame((new DateTime())->add(new DateInterval('P30D'))->format('Y-m-d') . ' 00:00:00', $result['due_date']);
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

        $payments = new Payments();
        $payments->setCaptures([$capture]);

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payments);

        $authenticationResult = new AuthenticationResult();
        $authenticationResult->setLiabilityShift(AuthenticationResult::LIABILITY_SHIFT_POSSIBLE);

        $depositBankDetails = new DepositBankDetails();
        $depositBankDetails->setAccountHolderName('Max Mustermann');
        $depositBankDetails->setBankName('Muster Bank');
        $depositBankDetails->setBic('any BIC');
        $depositBankDetails->setIban('any IBAN');

        $payUponInvoice = new PayUponInvoice();
        $payUponInvoice->setPaymentReference('ABC123');
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
