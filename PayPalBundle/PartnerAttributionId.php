<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle;

class PartnerAttributionId
{
    /**
     * Shopware Partner Id for PayPal Classic
     */
    const PAYPAL_CLASSIC = 'Shopware_Cart_EC_5native';

    /**
     * Shopware Partner Id for PayPal Plus
     */
    const PAYPAL_PLUS = 'Shopware_Cart_Plus_5native';

    /**
     * Shopware Partner Id for PayPal Installments
     */
    const PAYPAL_INSTALLMENTS = 'Shopware_Cart_Rate_5native';

    /**
     * Shopware Partner Id for PayPal Express Checkout
     */
    const PAYPAL_EXPRESS_CHECKOUT = 'Shopware_Cart_ECS_5native';
}
