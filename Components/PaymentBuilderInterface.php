<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

interface PaymentBuilderInterface
{
    /**
     * The function returns an array with all parameters that are expected by the PayPal API.
     *
     * @param PaymentBuilderParameters $params
     *
     * @return Payment
     */
    public function getPayment(PaymentBuilderParameters $params);
}
