<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Doctrine\DBAL\Connection;
use Generator;
use ReflectionClass;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
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
        $paymentMethods = PaymentMethodProvider::getAllUnifiedNames();
        $deactivatedPaymentMethods = PaymentMethodProvider::getDeactivatedPaymentMethods();
        $paymentMethodProvider = $this->createDummyPaymentMethodProvider();
        foreach ($paymentMethods as $paymentMethod) {
            if (\in_array($paymentMethod, $deactivatedPaymentMethods, true)) {
                continue;
            }

            yield 'Expect ' . $paymentMethod => [
                $paymentMethod,
                $paymentMethodProvider->getPaymentTypeByName($paymentMethod),
            ];
        }

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

    /**
     * @return PaymentMethodProviderInterface
     */
    private function createDummyPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            $this->createMock(Connection::class),
            $this->createMock(ModelManager::class)
        );
    }
}
