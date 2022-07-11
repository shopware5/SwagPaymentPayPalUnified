<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card\AuthenticationResult;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Card extends PayPalApiStruct
{
    const TYPE_CREDIT = 'CREDIT';
    const TYPE_UNKNOWN = 'UNKNOWN';

    /**
     * @var string
     * @phpstan-var numeric-string
     */
    private $lastDigits;

    /**
     * @var string
     */
    private $brand;

    /**
     * @var string
     * @phpstan-var Card::TYPE_*
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
     * @param string $lastDigits
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
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
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
     */
    public function setAuthenticationResult($authenticationResult)
    {
        $this->authenticationResult = $authenticationResult;
    }
}
