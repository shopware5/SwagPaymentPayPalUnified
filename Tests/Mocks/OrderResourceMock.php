<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Capture;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\CaptureOrder;
use SwagPaymentPayPalUnified\Tests\Mocks\ResultSet\VoidOrder;

class OrderResourceMock extends OrderResource
{
    const PAYPAL_PAYMENT_ID = 'PAY-4PX53149M52862435LWUYHZX';
    const THROW_EXCEPTION = PaymentResourceMock::THROW_EXCEPTION;

    public function __construct()
    {
    }

    public function capture($id, Capture $capture)
    {
        if ($id === self::THROW_EXCEPTION) {
            throw new \RuntimeException('test exception');
        }

        return CaptureOrder::get();
    }

    public function void($id)
    {
        if ($id === self::THROW_EXCEPTION) {
            throw new \RuntimeException('test exception');
        }

        return VoidOrder::get();
    }
}
