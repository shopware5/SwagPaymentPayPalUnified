<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class PaymentSource extends PayPalApiStruct
{
    /**
     * @var PayUponInvoice|null
     */
    protected $payUponInvoice;

    /**
     * @return PayUponInvoice|null
     */
    public function getPayUponInvoice()
    {
        return $this->payUponInvoice;
    }

    /**
     * @param PayUponInvoice|null $payUponInvoice
     */
    public function setPayUponInvoice($payUponInvoice)
    {
        $this->payUponInvoice = $payUponInvoice;
    }
}
