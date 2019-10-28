<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\PayPalBundle\Resources\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Capture;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\CaptureAuthorization;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\VoidAuthorization;

class AuthorizationResourceMock extends AuthorizationResource
{
    const PAYPAL_PAYMENT_ID = 'PAYID-LWUWSTI3EB47859H72718944';
    const THROW_EXCEPTION = PaymentResourceMock::THROW_EXCEPTION;

    public function __construct()
    {
    }

    public function capture($id, Capture $capture)
    {
        if ($id === self::THROW_EXCEPTION) {
            throw new \RuntimeException('test exception');
        }

        return CaptureAuthorization::get();
    }

    public function void($id)
    {
        if ($id === self::THROW_EXCEPTION) {
            throw new \RuntimeException('test exception');
        }

        return VoidAuthorization::get();
    }
}
