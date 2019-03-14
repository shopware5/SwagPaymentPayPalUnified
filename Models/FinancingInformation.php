<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * This model stores several information about the payment instructions that are being provided from
 * PayPal during a payment execution.
 *
 * @ORM\Entity()
 * @ORM\Table(name="swag_payment_paypal_unified_financing_information")
 */
class FinancingInformation extends ModelEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="temporaryID")
     *
     * @var \Shopware\Models\Order\Order
     */
    protected $order;
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_id", type="string", nullable=false, length=30)
     */
    private $paymentId;

    /**
     * @var float
     *
     * @ORM\Column(name="fee_amount", type="float", nullable=false)
     */
    private $feeAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="total_cost", type="float", nullable=false)
     */
    private $totalCost;

    /**
     * @var int
     *
     * @ORM\Column(name="term", type="integer", nullable=false)
     */
    private $term;

    /**
     * @var float
     *
     * @ORM\Column(name="monthly_payment", type="float", nullable=false)
     */
    private $monthlyPayment;

    /**
     * Returns the id of this entity
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the paymentID of the payment
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Returns the fee amount of the payment
     */
    public function getFeeAmount()
    {
        return $this->feeAmount;
    }

    /**
     * Returns the total cost of the payment
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * Returns the term of this payment
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Returns the monthly payment for this payment
     *
     * @return float
     */
    public function getMonthlyPayment()
    {
        return $this->monthlyPayment;
    }

    /**
     * Returns the order model for this payment
     *
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets the order id of this payment
     *
     * @param string $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Sets the fee amount of this payment
     *
     * @param $feeAmount
     */
    public function setFeeAmount($feeAmount)
    {
        $this->feeAmount = $feeAmount;
    }

    /**
     * Sets the total cost of this payment
     *
     * @param $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    /**
     * Sets the term of this payment
     *
     * @param $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    /**
     * Sets the monthly payment for this payment
     *
     * @param $monthlyPayment
     */
    public function setMonthlyPayment($monthlyPayment)
    {
        $this->monthlyPayment = $monthlyPayment;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
