<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\PayPalOrderParameter;

use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

interface PayPalOrderParameterFacadeInterface
{
    /**
     * @param PaymentType::* $paymentType
     *
     * @return PayPalOrderParameter
     */
    public function createPayPalOrderParameter($paymentType, ShopwareOrderData $shopwareOrderData);
}
