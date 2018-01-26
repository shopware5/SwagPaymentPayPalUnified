<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\ErrorResponse\Detail;

class ErrorResponse
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $informationLink;

    /**
     * @var string
     */
    private $debugId;

    /**
     * @var Detail[]
     */
    private $details;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getInformationLink()
    {
        return $this->informationLink;
    }

    /**
     * @param string $informationLink
     */
    public function setInformationLink($informationLink)
    {
        $this->informationLink = $informationLink;
    }

    /**
     * @return string
     */
    public function getDebugId()
    {
        return $this->debugId;
    }

    /**
     * @param string $debugId
     */
    public function setDebugId($debugId)
    {
        $this->debugId = $debugId;
    }

    /**
     * @return Detail[]
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param Detail[] $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @param array $data
     *
     * @return null|ErrorResponse
     */
    public static function fromArray(array $data)
    {
        if (!$data) {
            return null;
        }

        $result = new self();
        $result->setName($data['name']);
        $result->setMessage($data['message']);
        $result->setInformationLink($data['information_link']);
        $result->setDebugId($data['debug_id']);
        $details = [];
        foreach ($data['details'] as $detail) {
            $details[] = Detail::fromArray($detail);
        }
        $result->setDetails($details);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
