<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Front;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class Order implements SubscriberInterface
{
    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    public function __construct(Enlight_Controller_Front $front, Connection $connection)
    {
        $this->front = $front;
        $this->connection = $connection;
        $this->paymentMethodProvider = new PaymentMethodProvider();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Order_SaveOrder_FilterAttributes' => 'onFilterOrderAttributes',
        ];
    }

    public function onFilterOrderAttributes(\Enlight_Event_EventArgs $args)
    {
        $orderParams = $args->get('orderParams');
        $payPalPaymentId = $this->paymentMethodProvider->getPaymentId($this->connection);
        if ((int) $orderParams['paymentID'] !== $payPalPaymentId) {
            return;
        }

        $paymentType = $this->getPaymentType();
        if ($paymentType === null) {
            return;
        }

        $attributes = $args->getReturn();
        $attributes['swag_paypal_unified_payment_type'] = $paymentType;

        $args->setReturn($attributes);
    }

    /**
     * @return string|null
     */
    private function getPaymentType()
    {
        $request = $this->front->Request();
        if ($request === null) {
            return null;
        }

        $isPlus = (bool) $request->getParam('plus', false);
        $isExpressCheckout = (bool) $request->getParam('expressCheckout', false);
        $isSpbCheckout = (bool) $request->getParam('spbCheckout', false);
        $isInvoice = (bool) $request->getParam('invoiceCheckout', false);

        if ($isExpressCheckout) {
            return PaymentType::PAYPAL_EXPRESS;
        }

        if ($isSpbCheckout) {
            return PaymentType::PAYPAL_SMART_PAYMENT_BUTTONS;
        }

        if ($isInvoice) {
            return PaymentType::PAYPAL_INVOICE;
        }

        if ($isPlus) {
            return PaymentType::PAYPAL_PLUS;
        }

        return PaymentType::PAYPAL_CLASSIC;
    }
}
