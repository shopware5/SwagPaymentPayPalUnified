<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Backend;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentStatus;

trait OrderTrait
{
    /**
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * @param string $temporaryId
     *
     * @return int
     */
    protected function createOrder($temporaryId)
    {
        $orderStatus = $this->modelManager->getRepository(Status::class)->find(0);
        $paymentStatus = $this->modelManager->getRepository(Status::class)->find(PaymentStatus::PAYMENT_STATUS_OPEN);
        $dispatch = $this->modelManager->getRepository(Dispatch::class)->findOneBy([]);
        $shop = $this->modelManager->getRepository(Shop::class)->find(1);

        $paymentMethod = (new PaymentMethodProvider($this->modelManager))->getPaymentMethodModel();

        $order = new Order();
        $order->setOrderStatus($orderStatus);
        $order->setPaymentStatus($paymentStatus);
        $order->setTemporaryId($temporaryId);
        $order->setPayment($paymentMethod);
        $order->setDispatch($dispatch);
        $order->setShop($shop);
        $order->setInvoiceAmount(123.45);
        $order->setInvoiceAmountNet(111.0);
        $order->setInvoiceShipping(3.9);
        $order->setInvoiceShippingNet(2.9);
        $order->setTransactionId('');
        $order->setComment('');
        $order->setCustomerComment('');
        $order->setInternalComment('');
        $order->setNet(0);
        $order->setTaxFree(0);
        $order->setReferer('');
        $order->setTrackingCode('');
        $order->setLanguageSubShop($shop);
        $order->setCurrency(self::CURRENCY);
        $order->setCurrencyFactor(1.0);
        Shopware()->Models()->persist($order);
        Shopware()->Models()->flush($order);

        return $order->getId();
    }
}
