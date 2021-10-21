<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Address;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class Payer extends PayPalApiStruct
{
    /**
     * @var Name
     */
    protected $name;

    /**
     * @var string
     */
    protected $emailAddress;

    /**
     * @var string
     */
    protected $payerId;

    /**
     * @var Phone|null
     */
    protected $phone;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return void
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return void
     */
    public function setName(Name $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPayerId()
    {
        return $this->payerId;
    }

    /**
     * @param string $payerId
     *
     * @return void
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    }

    /**
     * @return \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param \SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone|null $phone
     *
     * @return void
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}
