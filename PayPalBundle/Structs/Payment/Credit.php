<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Credit\Price;

class Credit
{
    /**
     * @var Price
     */
    private $totalCost;

    /**
     * @var int
     */
    private $term;

    /**
     * @var Price
     */
    private $totalInterest;

    /**
     * @var Price
     */
    private $monthlyPayment;

    /**
     * @var bool
     */
    private $payerAcceptance;

    /**
     * @var bool
     */
    private $cartAmountImmutable;

    /**
     * @return Price
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param Price $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }

    /**
     * @return int
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param int $term
     */
    public function setTerm($term)
    {
        $this->term = $term;
    }

    /**
     * @return Price
     */
    public function getTotalInterest()
    {
        return $this->totalInterest;
    }

    /**
     * @param Price $totalInterest
     */
    public function setTotalInterest($totalInterest)
    {
        $this->totalInterest = $totalInterest;
    }

    /**
     * @return Price
     */
    public function getMonthlyPayment()
    {
        return $this->monthlyPayment;
    }

    /**
     * @param Price $monthlyPayment
     */
    public function setMonthlyPayment($monthlyPayment)
    {
        $this->monthlyPayment = $monthlyPayment;
    }

    /**
     * @return bool
     */
    public function isPayerAcceptance()
    {
        return $this->payerAcceptance;
    }

    /**
     * @param bool $payerAcceptance
     */
    public function setPayerAcceptance($payerAcceptance)
    {
        $this->payerAcceptance = $payerAcceptance;
    }

    /**
     * @return bool
     */
    public function isCartAmountImmutable()
    {
        return $this->cartAmountImmutable;
    }

    /**
     * @param bool $cartAmountImmutable
     */
    public function setCartAmountImmutable($cartAmountImmutable)
    {
        $this->cartAmountImmutable = $cartAmountImmutable;
    }

    /**
     * @param array|null $data
     *
     * @return null|Credit
     */
    public static function fromArray(array $data = null)
    {
        if (!$data) {
            return null;
        }

        $result = new self();
        $result->setTotalCost(Price::fromArray($data['total_cost']));
        $result->setTerm($data['term']);
        $result->setMonthlyPayment(Price::fromArray($data['monthly_payment']));
        $result->setTotalInterest(Price::fromArray($data['total_interest']));
        $result->setPayerAcceptance($data['payer_acceptance']);
        $result->setCartAmountImmutable($data['cart_amount_immutable']);

        return $result;
    }
}
