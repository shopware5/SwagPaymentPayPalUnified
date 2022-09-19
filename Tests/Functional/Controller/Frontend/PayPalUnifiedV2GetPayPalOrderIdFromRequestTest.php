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
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;
use SwagPaymentPayPalUnified\Tests\Unit\PaypalPaymentControllerTestCase;

class PayPalUnifiedV2GetPayPalOrderIdFromRequestTest extends PaypalPaymentControllerTestCase
{
    use ReflectionHelperTrait;

    /**
     * @dataProvider getPayPalOrderIdFromRequestTestDataProvider
     *
     * @param bool   $useInContext
     * @param string $expectedResult
     *
     * @return void
     */
    public function testGetPayPalOrderIdFromRequest($useInContext, $expectedResult)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        if ($useInContext) {
            $request->setParam('paypalOrderId', $expectedResult);
        } else {
            $request->setParam('token', $expectedResult);
        }

        $paypalUnifiedV2Controller = $this->getController(
            Shopware_Controllers_Frontend_PaypalUnifiedV2::class,
            [],
            $request
        );

        $reflectionMethod = $this->getReflectionMethod(Shopware_Controllers_Frontend_PaypalUnifiedV2::class, 'getPayPalOrderIdFromRequest');

        $result = $reflectionMethod->invoke($paypalUnifiedV2Controller, $useInContext);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getPayPalOrderIdFromRequestTestDataProvider()
    {
        yield 'Use inContext' => [
            true,
            'thisIsAPayPalOrderId',
        ];

        yield 'Use not inContext' => [
            false,
            'thisIsAPayPalToken',
        ];
    }
}
