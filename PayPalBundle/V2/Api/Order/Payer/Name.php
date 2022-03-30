<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Name extends PayPalApiStruct
{
    /**
     * @var string
     */
    protected $givenName;

    /**
     * @var string
     */
    protected $surname;

    /**
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * @param string $givenName
     *
     * @return void
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return void
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }
}
