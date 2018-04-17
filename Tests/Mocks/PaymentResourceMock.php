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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
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
            return [
                'credit_financing_offered' => [
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
                ],
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function create(Payment $payment)
    {
        if ($payment->getTransactions()->getAmount()->getCurrency() === 'throwException') {
            throw new RequestException('exception');
        }

        return [
            'id' => 'PAY-9HW62735H82101921LLK3D4I',
            'intent' => 'sale',
            'state' => 'created',
            'payer' => [
                'payment_method' => 'paypal',
            ],
            'transactions' => [
                0 => [
                    'amount' => [
                        'total' => '301.15',
                        'currency' => 'EUR',
                        'details' => [
                            'subtotal' => '297.25',
                            'tax' => '0.00',
                            'shipping' => '3.90',
                        ],
                    ],
                    'item_list' => [
                        'items' => [
                            0 => [
                                'name' => 'Strandtuch "Ibiza"',
                                'sku' => 'SW10178',
                                'price' => '19.95',
                                'currency' => 'EUR',
                                'quantity' => 15,
                            ],
                            1 => [
                                'name' => 'Warenkorbrabatt',
                                'sku' => 'SHIPPINGDISCOUNT',
                                'price' => '-2.00',
                                'currency' => 'EUR',
                                'quantity' => 1,
                            ],
                        ],
                    ],
                    'related_resources' => [],
                ],
            ],
            'experience_profile_id' => 'XP-H9SZ-G664-VUF4-NN3S',
            'create_time' => '2018-04-17T08:36:01Z',
            'links' => [
                0 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-9HW62735H82101921LLK3D4I',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                1 => [
                    'href' => 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-49W9096312907153R',
                    'rel' => 'approval_url',
                    'method' => 'REDIRECT',
                ],
                2 => [
                    'href' => 'https://api.sandbox.paypal.com/v1/payments/payment/PAY-9HW62735H82101921LLK3D4I/execute',
                    'rel' => 'execute',
                    'method' => 'POST',
                ],
            ],
        ];
    }
}
