<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;

class Card extends AbstractPaymentSource
{
    const TYPE_CREDIT = 'CREDIT';
    const TYPE_UNKNOWN = 'UNKNOWN';

    /**
     * @var numeric-string
     */
    private $lastDigits;

    /**
     * @var string
     */
    private $brand;

    /**
     * @var Card::TYPE_*
     */
    private $type;

    /**
     * @var AuthenticationResult|null
     */
    private $authenticationResult;

    /**
     * @return string
     */
    public function getLastDigits()
    {
        return $this->lastDigits;
    }

    /**
     * @param numeric-string $lastDigits
     *
     * @return void
     */
    public function setLastDigits($lastDigits)
    {
        $this->lastDigits = $lastDigits;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     *
     * @return void
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return Card::TYPE_*
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Card::TYPE_* $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return AuthenticationResult|null
     */
    public function getAuthenticationResult()
    {
        return $this->authenticationResult;
    }

    /**
     * @param AuthenticationResult|null $authenticationResult
     *
     * @return void
     */
    public function setAuthenticationResult($authenticationResult)
    {
        $this->authenticationResult = $authenticationResult;
    }
}
