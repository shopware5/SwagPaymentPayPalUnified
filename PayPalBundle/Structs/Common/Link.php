<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Common;

class Link
{
    /**
     * @var string
     */
    private $href;

    /**
     * @var string
     */
    private $rel;

    /**
     * @var string
     */
    private $method;

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param string $href
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
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'href' => $this->getHref(),
            'rel' => $this->getRel(),
            'method' => $this->getMethod(),
        ];
    }

    /**
     * @return Link
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->setHref($data['href']);
        $result->setRel($data['rel']);
        $result->setMethod($data['method']);

        return $result;
    }
}
