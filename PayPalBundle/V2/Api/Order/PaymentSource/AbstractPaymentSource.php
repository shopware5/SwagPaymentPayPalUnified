<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

abstract class AbstractPaymentSource extends PayPalApiStruct
{
    /**
     * @var ExperienceContext|null
     */
    protected $experienceContext;

    /**
     * @return ExperienceContext|null
     */
    public function getExperienceContext()
    {
        return $this->experienceContext;
    }

    /**
     * @return void
     */
    public function setExperienceContext(ExperienceContext $experienceContext)
    {
        $this->experienceContext = $experienceContext;
    }
}
