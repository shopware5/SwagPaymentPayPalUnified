<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\PayPalOrderParameter;

/**
 * @phpstan-import-type CheckoutBasketArray from \Shopware_Controllers_Frontend_Checkout
 */
class ShopwareOrderData
{
    /**
     * @var array<string, mixed>
     */
    private $shopwareUserData;

    /**
     * @var array<string, mixed>
     *
     * @phpstan-var CheckoutBasketArray
     */
    private $shopwareBasketData;

    /**
     * @param array<string, mixed> $shopwareUserData
     * @param array<string, mixed> $shopwareBasketData
     *
     * @phpstan-param CheckoutBasketArray $shopwareBasketData
     */
    public function __construct(array $shopwareUserData, array $shopwareBasketData)
    {
        $this->shopwareUserData = $shopwareUserData;
        $this->shopwareBasketData = $shopwareBasketData;
    }

    /**
     * @return array<string, mixed>
     */
    public function getShopwareUserData()
    {
        return $this->shopwareUserData;
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return CheckoutBasketArray
     */
    public function getShopwareBasketData()
    {
        return $this->shopwareBasketData;
    }
}
