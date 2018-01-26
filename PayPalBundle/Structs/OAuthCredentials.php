<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

class OAuthCredentials
{
    /**
     * @var string
     */
    private $restId;

    /**
     * @var string
     */
    private $restSecret;

    /**
     * @return string
     */
    public function getRestId()
    {
        return $this->restId;
    }

    /**
     * @param string $restId
     */
    public function setRestId($restId)
    {
        $this->restId = $restId;
    }

    /**
     * @return string
     */
    public function getRestSecret()
    {
        return $this->restSecret;
    }

    /**
     * @param string $restSecret
     */
    public function setRestSecret($restSecret)
    {
        $this->restSecret = $restSecret;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'Basic ' . base64_encode($this->restId . ':' . $this->restSecret);
    }
}
