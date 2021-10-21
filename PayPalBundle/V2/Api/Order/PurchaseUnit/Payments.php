<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Payments extends PayPalApiStruct
{
    /**
     * @var Authorization[]|null
     */
    protected $authorizations;

    /**
     * @var Capture[]|null
     */
    protected $captures;

    /**
     * @var Refund[]|null
     */
    protected $refunds;

    /**
     * @return Authorization[]|null
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * @param Authorization[]|null $authorizations
     *
     * @return void
     */
    public function setAuthorizations($authorizations)
    {
        $this->authorizations = $authorizations;
    }

    /**
     * @return Capture[]|null
     */
    public function getCaptures()
    {
        return $this->captures;
    }

    /**
     * @param Capture[]|null $captures
     *
     * @return void
     */
    public function setCaptures($captures)
    {
        $this->captures = $captures;
    }

    /**
     * @return Refund[]|null
     */
    public function getRefunds()
    {
        return $this->refunds;
    }

    /**
     * @param Refund[]|null $refunds
     *
     * @return void
     */
    public function setRefunds($refunds)
    {
        $this->refunds = $refunds;
    }
}
