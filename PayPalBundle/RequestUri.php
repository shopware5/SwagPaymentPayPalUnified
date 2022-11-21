<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle;

final class RequestUri
{
    const PAYMENT_RESOURCE = 'v1/payments/payment';
    const WEBHOOK_RESOURCE = 'v1/notifications/webhooks';
    const TOKEN_RESOURCE = 'v1/oauth2/token';
    const SALE_RESOURCE = 'v1/payments/sale';
    const REFUND_RESOURCE = 'v1/payments/refund';
    const AUTHORIZATION_RESOURCE = 'v1/payments/authorization';
    const CAPTURE_RESOURCE = 'v1/payments/capture';
    const ORDER_RESOURCE = 'v1/payments/orders';
    const CREDENTIALS_RESOURCE = 'v1/customer/partners/%s/merchant-integrations/credentials';
    const MERCHANT_INTEGRATIONS_RESOURCE = 'v1/customer/partners/%s/merchant-integrations/%s';
    const USER_INFO_RESOURCE = 'v1/identity/oauth2/userinfo';
    const SHIPPING_RESOURCE = 'v1/shipping';

    private function __construct()
    {
    }
}
