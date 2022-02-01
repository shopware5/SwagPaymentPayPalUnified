<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;

class Amount extends AbstractAmount
{
    /**
     * @var Breakdown|null
     */
    protected $breakdown;

    /**
     * @return Breakdown|null
     */
    public function getBreakdown()
    {
        return $this->breakdown;
    }

    /**
     * @param Breakdown|null $breakdown
     *
     * @return void
     */
    public function setBreakdown($breakdown)
    {
        $this->breakdown = $breakdown;
    }
}
