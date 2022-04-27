<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentControllerResults;

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
     * @param bool   $success
     * @param string $shopwareOrderNumber
     */
    public function __construct($success, $shopwareOrderNumber)
    {
        $this->success = $success;
        $this->shopwareOrderNumber = $shopwareOrderNumber;
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
}
