<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend;

use Enlight_Controller_Request_RequestTestCase;
use Generator;
use Shopware_Controllers_Frontend_PaypalUnifiedV2;
use stdClass;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PayPalUnifiedV2GetPayPalOrderIdFromRequestTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;

    /**
     * @dataProvider getPayPalOrderIdFromRequestTestDataProvider
     *
     * @param stdClass    $keyValuePair
     * @param string|null $expectedResult
     *
     * @return void
     */
    public function testGetPayPalOrderIdFromRequest($keyValuePair, $expectedResult)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam($keyValuePair->key, $keyValuePair->value);

        $paypalUnifiedV2Controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [],
            $request
        );

        $reflectionMethod = $this->getReflectionMethod(Shopware_Controllers_Frontend_PaypalUnifiedV2::class, 'getPayPalOrderIdFromRequest');

        $result = $reflectionMethod->invoke($paypalUnifiedV2Controller);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getPayPalOrderIdFromRequestTestDataProvider()
    {
        yield 'Use inContext' => [
            $this->createKeyValuePairForRequest('paypalOrderId', 'thisIsAPayPalOrderId'),
            'thisIsAPayPalOrderId',
        ];

        yield 'Use not inContext' => [
            $this->createKeyValuePairForRequest('token', 'thisIsAPayPalToken'),
            'thisIsAPayPalToken',
        ];

        yield 'Set nonsense' => [
            $this->createKeyValuePairForRequest('nonsense', 'thisIsAPayPalToken'),
            null,
        ];
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return stdClass
     */
    private function createKeyValuePairForRequest($key, $value)
    {
        $keyValuePair = new stdClass();
        $keyValuePair->key = $key;
        $keyValuePair->value = $value;

        return $keyValuePair;
    }
}
