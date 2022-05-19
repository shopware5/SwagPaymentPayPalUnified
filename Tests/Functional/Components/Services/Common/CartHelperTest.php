<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Common;

use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;

class CartHelperTest extends TestCase
{
    /**
     * @dataProvider getTotalAmountTestDataProvider
     *
     * @param array<string,mixed> $cart
     * @param array<string,mixed> $customer
     * @param string              $expectedResult
     *
     * @return void
     */
    public function testGetTotalAmount(array $cart, array $customer, $expectedResult)
    {
        $cartHelper = $this->createCartHelper();

        $result = $cartHelper->getTotalAmount($cart, $customer);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function getTotalAmountTestDataProvider()
    {
        yield 'User charge vat and use gross prices' => [
            ['AmountNumeric' => '1.99'],
            ['additional' => ['show_net' => true, 'charge_vat' => true]],
            '1.99',
        ];

        yield 'User charge vat and use net prices' => [
            ['AmountWithTaxNumeric' => '2.99'],
            ['additional' => ['show_net' => false, 'charge_vat' => true]],
            '2.99',
        ];

        yield 'User dont charge vat and use gross prices' => [
            ['AmountNetNumeric' => '3.99'],
            ['additional' => ['show_net' => false, 'charge_vat' => false]],
            '3.99',
        ];

        yield 'User dont charge vat and use net prices' => [
            ['AmountNetNumeric' => '4.99'],
            ['additional' => ['show_net' => true, 'charge_vat' => false, 'countryShipping' => ['taxfree' => true]]],
            '4.99',
        ];
    }

    /**
     * @return CartHelper
     */
    private function createCartHelper()
    {
        return new CartHelper(new CustomerHelper(), new PriceFormatter());
    }
}
