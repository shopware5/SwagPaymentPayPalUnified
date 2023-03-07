<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;

use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Address;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice\DepositBankDetails;

class PayUponInvoice extends AbstractPaymentSource
{
    /**
     * @var Name
     */
    protected $name;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $birthDate;

    /**
     * @var PhoneNumber
     */
    protected $phone;

    /**
     * @var Address
     */
    protected $billingAddress;

    /**
     * @var string
     */
    protected $paymentReference;

    /**
     * @var DepositBankDetails
     */
    protected $depositBankDetails;

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param string $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return PhoneNumber
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param PhoneNumber $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return string
     */
    public function getPaymentReference()
    {
        return $this->paymentReference;
    }

    /**
     * @param string $paymentReference
     *
     * @return void
     */
    public function setPaymentReference($paymentReference)
    {
        $this->paymentReference = $paymentReference;
    }

    /**
     * @return DepositBankDetails
     */
    public function getDepositBankDetails()
    {
        return $this->depositBankDetails;
    }

    /**
     * @param DepositBankDetails $depositBankDetails
     *
     * @return void
     */
    public function setDepositBankDetails($depositBankDetails)
    {
        $this->depositBankDetails = $depositBankDetails;
    }
}
