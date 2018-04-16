<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use SwagPaymentPayPalUnified\Components\Services\Installments\OrderCreditInfoService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Credit;

class OrderCreditInfoServiceMock extends OrderCreditInfoService
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function saveCreditInfo(Credit $credit, $paymentId)
    {
    }
}
