<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse\FinancingOption\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse\FinancingOption\CreditFinancing;

class FinancingOption
{
    /**
     * @var CreditFinancing
     */
    private $creditFinancing;

    /**
     * @var Amount
     */
    private $minAmount;

    /**
     * @var float
     */
    private $monthlyPercentageRate;

    /**
     * @var Amount
     */
    private $monthlyPayment;

    /**
     * @var Amount
     */
    private $totalInterest;

    /**
     * @var Amount
     */
    private $totalCost;

    /**
     * @return CreditFinancing
     */
    public function getCreditFinancing()
    {
        return $this->creditFinancing;
    }

    /**
     * @param CreditFinancing $creditFinancing
     */
    public function setCreditFinancing($creditFinancing)
    {
        $this->creditFinancing = $creditFinancing;
    }

    /**
     * @return Amount
     */
    public function getMinAmount()
    {
        return $this->minAmount;
    }

    /**
     * @param Amount $minAmount
     */
    public function setMinAmount($minAmount)
    {
        $this->minAmount = $minAmount;
    }

    /**
     * @return float
     */
    public function getMonthlyPercentageRate()
    {
        return $this->monthlyPercentageRate;
    }

    /**
     * @param float $monthlyPercentageRate
     */
    public function setMonthlyPercentageRate($monthlyPercentageRate)
    {
        $this->monthlyPercentageRate = $monthlyPercentageRate;
    }

    /**
     * @return Amount
     */
    public function getMonthlyPayment()
    {
        return $this->monthlyPayment;
    }

    /**
     * @param Amount $monthlyPayment
     */
    public function setMonthlyPayment($monthlyPayment)
    {
        $this->monthlyPayment = $monthlyPayment;
    }

    /**
     * @return Amount
     */
    public function getTotalInterest()
    {
        return $this->totalInterest;
    }

    /**
     * @param Amount $totalInterest
     */
    public function setTotalInterest($totalInterest)
    {
        $this->totalInterest = $totalInterest;
    }

    /**
     * @return Amount
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param Amount $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'creditFinancing' => $this->getCreditFinancing()->toArray(),
            'minAmount' => $this->getMinAmount()->toArray(),
            'monthlyPercentageRate' => $this->getMonthlyPercentageRate(),
            'monthlyPayment' => $this->getMonthlyPayment()->toArray(),
            'totalInterest' => $this->getTotalInterest()->toArray(),
            'totalCost' => $this->getTotalCost()->toArray(),
        ];
    }

    /**
     * @param array $data
     *
     * @return FinancingOption
     */
    public static function fromArray(array $data = [])
    {
        $financingOption = new self();
        $financingOption->setCreditFinancing(CreditFinancing::fromArray($data['credit_financing']));
        $financingOption->setMinAmount(Amount::fromArray($data['min_amount']));
        $financingOption->setMonthlyPercentageRate((float) $data['monthly_percentage_rate']);
        $financingOption->setMonthlyPayment(Amount::fromArray($data['monthly_payment']));
        $financingOption->setTotalInterest(Amount::fromArray($data['total_interest']));
        $financingOption->setTotalCost(Amount::fromArray($data['total_cost']));

        return $financingOption;
    }
}
