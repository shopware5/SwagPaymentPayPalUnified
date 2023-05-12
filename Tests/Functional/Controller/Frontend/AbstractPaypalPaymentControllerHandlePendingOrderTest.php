<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Shopware\Models\Order\Order as ShopwareOrder;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Functional\UnifiedControllerTestCase;

class AbstractPaypalPaymentControllerHandlePendingOrderTest extends UnifiedControllerTestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    const PAYPAL_ORDER_ID = 'anyPayPalOrderId';

    const CAPTURE_ID = 'anyCaptureId';

    /**
     * @before
     *
     * @return void
     */
    public function setSessionData()
    {
        $userData = require __DIR__ . '/_fixtures/getUser_result.php';
        $cartData = require __DIR__ . '/_fixtures/getBasket_result.php';

        $session = $this->getContainer()->get('session');
        $session->offsetSet(
            'sOrderVariables',
            [
                'sUserData' => $userData,
                'sBasket' => $cartData,
            ]
        );
    }

    /**
     * @after
     *
     * @return void
     */
    public function clearSession()
    {
        $session = $this->getContainer()->get('session');

        if (method_exists($session, 'clear')) {
            $session->clear();
        } else {
            $session->offsetUnset('sUserId');
            $session->offsetUnset('sOrderVariables');
        }
    }

    /**
     * @return void
     */
    public function testHandlePendingOrder()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/temporary_order.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $order = $this->createPendingPayPalOrder();
        $order->setId(self::PAYPAL_ORDER_ID);

        $this->getController(AbstractPaypalPaymentControllerTestMock::class)
            ->handlePendingOrder($order);

        $orderNumber = $this->getContainer()->get('session')->offsetGet('sOrderVariables');

        $entityManager = $this->getContainer()->get('models');
        $order = $entityManager->getRepository(ShopwareOrder::class)->findOneBy(['number' => $orderNumber]);

        static::assertInstanceOf(ShopwareOrder::class, $order);
        static::assertSame(Status::PAYMENT_STATE_OPEN, $order->getPaymentStatus()->getId());

        $this->getContainer()->get('dbal_connection')->delete('s_order', ['transactionID' => self::PAYPAL_ORDER_ID]);
    }

    /**
     * @return Order
     */
    private function createPendingPayPalOrder()
    {
        $payment = new Payments();

        $capture = new Capture();
        $capture->setStatus(PaymentStatusV2::ORDER_CAPTURE_PENDING);
        $capture->setId(self::CAPTURE_ID);
        $payment->setCaptures([$capture]);

        $purchaseUnit = new PurchaseUnit();
        $purchaseUnit->setPayments($payment);

        $order = new Order();
        $order->setPurchaseUnits([$purchaseUnit]);
        $order->setId(self::PAYPAL_ORDER_ID);

        return $order;
    }
}

class AbstractPaypalPaymentControllerTestMock extends AbstractPaypalPaymentController
{
    /**
     * @return void
     */
    public function handlePendingOrder(Order $payPalOrder)
    {
        parent::handlePendingOrder($payPalOrder);
    }
}
