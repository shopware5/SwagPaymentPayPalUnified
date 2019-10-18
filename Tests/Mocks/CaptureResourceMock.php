<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\PayPalBundle\Resources\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\CaptureRefund;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\RefundCapture;

class CaptureResourceMock extends CaptureResource
{
    const PAYPAL_PAYMENT_ID = AuthorizationResourceMock::PAYPAL_PAYMENT_ID;
    const THROW_EXCEPTION = PaymentResourceMock::THROW_EXCEPTION;

    public function __construct()
    {
    }

    public function refund($id, CaptureRefund $refund)
    {
        if ($id === self::THROW_EXCEPTION) {
            throw new \RuntimeException('test exception');
        }

        return RefundCapture::get();
    }
}
