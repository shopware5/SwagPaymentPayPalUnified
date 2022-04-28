<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults;

use Shopware\Models\Order\Basket;

class HandleOrderWithSendOrderNumberResult
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var string
     */
    private $shopwareOrderNumber;

    /**
     * @var array<int,Basket>
     */
    private $cartData;

    /**
     * @param bool              $success
     * @param string            $shopwareOrderNumber
     * @param array<int,Basket> $cartData
     */
    public function __construct($success, $shopwareOrderNumber, array $cartData = [])
    {
        $this->success = $success;
        $this->shopwareOrderNumber = $shopwareOrderNumber;
        $this->cartData = $cartData;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getShopwareOrderNumber()
    {
        return $this->shopwareOrderNumber;
    }

    /**
     * @return array<int,Basket>
     */
    public function getCartData()
    {
        return $this->cartData;
    }
}
