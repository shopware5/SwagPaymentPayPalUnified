<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Generator;
use ReflectionClass;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class AbstractPaypalPaymentControllerGetPaymentTypeTest extends PaypalPaymentControllerTestCase
{
    use ShopRegistrationTrait;

    const ANY_PAYMENT_NAME = 'anyPaymentName';
    const ANY_PAYMENT_ID = 1;

    /**
     * @dataProvider getPaymentTypeTestDataProvider
     *
     * @param PaymentMethodProviderInterface::* $paymentName
     * @param PaymentType::*                    $expectedResult
     *
     * @return void
     */
    public function testGetPaymentType($paymentName, $expectedResult)
    {
        $this->prepareSession($paymentName);

        $controller = $this->getController(AbstractPaypalPaymentController::class, [
            self::SERVICE_PAYMENT_METHOD_PROVIDER => $this->getContainer()->get('paypal_unified.payment_method_provider'),
        ]);

        $reflectionMethod = (new ReflectionClass(AbstractPaypalPaymentController::class))->getMethod('getPaymentType');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invoke($controller);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getPaymentTypeTestDataProvider()
    {
        yield 'Expect PAYPAL_SEPA' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_SEPA_METHOD_NAME,
            PaymentType::PAYPAL_SEPA,
        ];

        yield 'Expect PAYPAL_ADVANCED_CREDIT_DEBIT_CARD' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_ADVANCED_CREDIT_DEBIT_CARD_METHOD_NAME,
            PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
        ];

        yield 'Expect PAYPAL_PAY_LATER' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME,
            PaymentType::PAYPAL_PAY_LATER,
        ];

        yield 'Expect PAYPAL_CLASSIC_V2' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
            PaymentType::PAYPAL_CLASSIC_V2,
        ];

        yield 'Expect PAYPAL_PAY_UPON_INVOICE_V2' => [
            PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
            PaymentType::PAYPAL_PAY_UPON_INVOICE_V2,
        ];

        yield 'Expect APM_BANCONTACT' => [
            PaymentMethodProviderInterface::BANCONTACT_METHOD_NAME,
            PaymentType::APM_BANCONTACT,
        ];

        yield 'Expect APM_BLIK' => [
            PaymentMethodProviderInterface::BLIK_METHOD_NAME,
            PaymentType::APM_BLIK,
        ];

        yield 'Expect APM_GIROPAY' => [
            PaymentMethodProviderInterface::GIROPAY_METHOD_NAME,
            PaymentType::APM_GIROPAY,
        ];

        yield 'Expect APM_IDEAL' => [
            PaymentMethodProviderInterface::IDEAL_METHOD_NAME,
            PaymentType::APM_IDEAL,
        ];

        yield 'Expect APM_MULTIBANCO' => [
            PaymentMethodProviderInterface::MULTIBANCO_METHOD_NAME,
            PaymentType::APM_MULTIBANCO,
        ];

        yield 'Expect APM_MYBANK' => [
            PaymentMethodProviderInterface::MY_BANK_METHOD_NAME,
            PaymentType::APM_MYBANK,
        ];

        yield 'Expect APM_P24' => [
            PaymentMethodProviderInterface::P24_METHOD_NAME,
            PaymentType::APM_P24,
        ];

        yield 'Expect APM_SOFORT' => [
            PaymentMethodProviderInterface::SOFORT_METHOD_NAME,
            PaymentType::APM_SOFORT,
        ];

        yield 'Expect APM_TRUSTLY' => [
            PaymentMethodProviderInterface::TRUSTLY_METHOD_NAME,
            PaymentType::APM_TRUSTLY,
        ];

        yield 'Expect APM_EPS' => [
            PaymentMethodProviderInterface::EPS_METHOD_NAME,
            PaymentType::APM_EPS,
        ];

        yield 'Expect Exception' => [
            self::ANY_PAYMENT_NAME,
            PaymentType::PAYPAL_CLASSIC_V2,
        ];
    }

    /**
     * @param string $paymentName
     *
     * @return void
     */
    private function prepareSession($paymentName)
    {
        $paymentMethodProvider = $this->getContainer()->get('paypal_unified.payment_method_provider');

        if ($paymentName === self::ANY_PAYMENT_NAME) {
            $paymentId = self::ANY_PAYMENT_ID;
        } else {
            $paymentId = $paymentMethodProvider->getPaymentId($paymentName);
        }

        $session = $this->getContainer()->get('session');
        $session->offsetSet(
            'sOrderVariables',
            [
                'sUserData' => [
                    'additional' => [
                        'payment' => [
                            'id' => $paymentId,
                        ],
                    ],
                ],
            ]
        );
    }
}
