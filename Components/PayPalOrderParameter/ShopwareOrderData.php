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
     * @var array
     */
    private $shopwareUserData;

    /**
     * @var array
     *
     * @phpstan-var CheckoutBasketArray
     */
    private $shopwareBasketData;

    /**
     * @phpstan-param CheckoutBasketArray $shopwareBasketData
     */
    public function __construct(array $shopwareUserData, array $shopwareBasketData)
    {
        $this->shopwareUserData = $shopwareUserData;
        $this->shopwareBasketData = $shopwareBasketData;
    }

    /**
     * @return array
     */
    public function getShopwareUserData()
    {
        return $this->shopwareUserData;
    }

    /**
     * @return array
     *
     * @phpstan-return CheckoutBasketArray
     */
    public function getShopwareBasketData()
    {
        return $this->shopwareBasketData;
    }
}
