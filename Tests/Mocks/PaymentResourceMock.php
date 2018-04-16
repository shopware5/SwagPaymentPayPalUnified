<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\InstallmentsTest;

class PaymentResourceMock extends PaymentResource
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function patch($paymentId, array $patches)
    {
        throw new RequestException('patch exception');
    }

    /**
     * {@inheritdoc}
     *
     * @throws RequestException
     */
    public function get($paymentId)
    {
        if ($paymentId === 'exception') {
            throw new RequestException('get exception');
        }

        if ($paymentId === InstallmentsTest::INSTALLMENTS_PAYMENT_ID) {
            return ['credit_financing_offered' => [
                'total_cost' => [
                    'value' => '486.57',
                    'currency' => 'EUR',
                ],
                'term' => 6,
                'monthly_payment' => [
                    'value' => '81.22',
                    'currency' => 'EUR',
                ],
                'total_interest' => [
                    'value' => '12.57',
                    'currency' => 'EUR',
                ],
                'payer_acceptance' => true,
                'cart_amount_immutable' => true,
            ]];
        }

        return [];
    }
}
