<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Patch extends PayPalApiStruct
{
    const OPERATION_ADD = 'add';
    const OPERATION_REPLACE = 'replace';
    const OPERATION_REMOVE = 'remove';

    /**
     * @var string
     */
    protected $op;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var int|float|string|bool|array|null
     */
    protected $value;

    /**
     * @var string
     */
    protected $from;

    /**
     * @return string
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @param string $op
     *
     * @return void
     */
    public function setOp($op)
    {
        $this->op = $op;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return array|bool|float|int|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|bool|float|int|string|null $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     *
     * @return void
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }
}
