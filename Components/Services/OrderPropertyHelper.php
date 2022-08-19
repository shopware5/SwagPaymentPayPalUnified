<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;

/**
 * @internal
 */
class OrderPropertyHelper
{
    /**
     * @return Capture|null
     */
    public function getFirstCapture(Order $paypalOrder)
    {
        $payments = $this->getPayments($paypalOrder);
        if ($payments === null) {
            return null;
        }

        $captures = $payments->getCaptures();
        if (!\is_array($captures)) {
            return null;
        }

        $capture = $captures[0];
        if (!$capture instanceof Capture) {
            return null;
        }

        return $capture;
    }

    /**
     * @return Authorization|null
     */
    public function getAuthorization(Order $paypalOrder)
    {
        $payments = $this->getPayments($paypalOrder);
        if ($payments === null) {
            return null;
        }

        $authorizations = $payments->getAuthorizations();
        if (!\is_array($authorizations)) {
            return null;
        }

        $authorization = $authorizations[0];
        if (!$authorization instanceof Authorization) {
            return null;
        }

        return $authorization;
    }

    /**
     * @return Payments|null
     */
    public function getPayments(Order $paypalOrder)
    {
        $purchaseUnits = $paypalOrder->getPurchaseUnits();
        if (!\is_array($purchaseUnits)) {
            return null;
        }

        $purchaseUnit = $purchaseUnits[0];
        if (!$purchaseUnit instanceof PurchaseUnit) {
            return null;
        }

        $payments = $purchaseUnit->getPayments();
        if (!$payments instanceof Payments) {
            return null;
        }

        return $payments;
    }

    /**
     * @return PayUponInvoice|null
     */
    public function getPayUponInvoice(Order $paypalOrder)
    {
        $paymentSource = $paypalOrder->getPaymentSource();
        if (!$paymentSource instanceof PaymentSource) {
            return null;
        }

        $payUponInvoice = $paymentSource->getPayUponInvoice();
        if (!$payUponInvoice instanceof PayUponInvoice) {
            return null;
        }

        return $payUponInvoice;
    }
}
