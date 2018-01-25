<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle;

class RequestUri
{
    const PAYMENT_RESOURCE = 'payments/payment';
    const PROFILE_RESOURCE = 'payment-experience/web-profiles';
    const WEBHOOK_RESOURCE = 'notifications/webhooks';
    const TOKEN_RESOURCE = 'oauth2/token';
    const SALE_RESOURCE = 'payments/sale';
    const REFUND_RESOURCE = 'payments/refund';
    const AUTHORIZATION_RESOURCE = 'payments/authorization';
    const CAPTURE_RESOURCE = 'payments/capture';
    const ORDER_RESOURCE = 'payments/orders';
    const FINANCING_RESOURCE = 'credit/calculated-financing-options';
}
