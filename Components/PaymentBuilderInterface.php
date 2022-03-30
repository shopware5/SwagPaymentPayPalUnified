<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components;

use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

interface PaymentBuilderInterface
{
    /**
     * @deprecated Will be removed. Use CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES instead
     */
    const CUSTOMER_GROUP_USE_GROSS_PRICES = CustomerHelper::CUSTOMER_GROUP_USE_GROSS_PRICES;

    /**
     * The function returns an array with all parameters that are expected by the PayPal API.
     *
     * @return Payment
     */
    public function getPayment(PaymentBuilderParameters $params);
}
