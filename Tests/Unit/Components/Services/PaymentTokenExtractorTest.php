<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Services;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\PaymentTokenExtractor;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class PaymentTokenExtractorTest extends TestCase
{
    public function testExtract()
    {
        $payment = new Payment();
        $selfLink = Link::fromArray([
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-3A3234483P2338009KTTFX7Q',
            'rel' => 'self',
            'method' => 'GET',
        ]);
        $approvalLink = Link::fromArray([
            'href' => 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-44X706219E3526258',
            'rel' => 'approval_url',
            'method' => 'REDIRECT',
        ]);
        $executeLink = Link::fromArray([
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-3A3234483P2338009KTTFX7Q/execute',
            'rel' => 'execute',
            'method' => 'POST',
        ]);
        $payment->setLinks([
            $selfLink,
            $approvalLink,
            $executeLink,
        ]);
        $token = PaymentTokenExtractor::extract($payment);
        static::assertSame('EC-44X706219E3526258', $token);
    }

    public function testExtractWithoutApprovalUrl()
    {
        $payment = new Payment();
        $selfLink = Link::fromArray([
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-3A3234483P2338009KTTFX7Q',
            'rel' => 'self',
            'method' => 'GET',
        ]);
        $executeLink = Link::fromArray([
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-3A3234483P2338009KTTFX7Q/execute',
            'rel' => 'execute',
            'method' => 'POST',
        ]);
        $payment->setLinks([
            $selfLink,
            $executeLink,
        ]);
        $token = PaymentTokenExtractor::extract($payment);
        static::assertSame('', $token);
    }

    public function testExtractWithInvalidApprovalUrl()
    {
        $payment = new Payment();
        $selfLink = Link::fromArray([
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-3A3234483P2338009KTTFX7Q',
            'rel' => 'self',
            'method' => 'GET',
        ]);
        $approvalLink = Link::fromArray([
            'href' => 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout',
            'rel' => 'approval_url',
            'method' => 'REDIRECT',
        ]);
        $executeLink = Link::fromArray([
            'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-3A3234483P2338009KTTFX7Q/execute',
            'rel' => 'execute',
            'method' => 'POST',
        ]);
        $payment->setLinks([
            $selfLink,
            $approvalLink,
            $executeLink,
        ]);
        $token = PaymentTokenExtractor::extract($payment);
        static::assertSame('', $token);
    }
}
