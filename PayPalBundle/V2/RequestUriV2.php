<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2;

final class RequestUriV2
{
    const AUTHORIZATIONS_RESOURCE = 'v2/payments/authorizations';
    const CAPTURES_RESOURCE = 'v2/payments/captures';
    const ORDERS_RESOURCE = 'v2/checkout/orders';
    const REFUNDS_RESOURCE = 'v2/payments/refunds';
    const CLIENT_TOKEN_RESOURCE = 'v1/identity/generate-token';

    private function __construct()
    {
    }
}
