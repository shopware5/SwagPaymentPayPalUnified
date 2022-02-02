<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

abstract class Link extends PayPalApiStruct
{
    const RELATION_APPROVE = 'approve';
    const RELATION_UP = 'up';
    const RELATION_PAYER_ACTION_REQUIRED = 'payer-action';

    /**
     * @var string
     */
    protected $href;

    /**
     * @var string
     */
    protected $rel;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string|null
     */
    protected $encType;

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param string $href
     *
     * @return void
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @param string $rel
     *
     * @return void
     */
    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string|null
     */
    public function getEncType()
    {
        return $this->encType;
    }

    /**
     * @param string|null $encType
     *
     * @return void
     */
    public function setEncType($encType)
    {
        $this->encType = $encType;
    }
}
