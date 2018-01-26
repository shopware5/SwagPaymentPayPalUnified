<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse;

class Detail
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $issue;

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * @param string $issue
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
    }

    /**
     * @param $detail
     *
     * @return Detail
     */
    public static function fromArray($detail)
    {
        $result = new self();
        $result->setField($detail['field']);
        $result->setIssue($detail['issue']);

        return $result;
    }
}
