<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Installments;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse\FinancingOption;

class FinancingOptionsHandler
{
    /**
     * Use this sort parameter to sort by the term of each rate.
     */
    const SORT_BY_TERM = 0;

    /**
     * Use this sort parameter to sort by the monthly payment of each rate.
     */
    const SORT_BY_MONTHLY_PAYMENT = 1;

    /**
     * @var FinancingResponse
     */
    private $financingResponse;

    /**
     * @param FinancingResponse $financingResponse
     */
    public function __construct(FinancingResponse $financingResponse)
    {
        $this->financingResponse = $financingResponse;
    }

    /**
     * @param int $sortParameter
     *
     * @return FinancingResponse
     */
    public function sortOptionsBy($sortParameter = self::SORT_BY_TERM)
    {
        if ($sortParameter === self::SORT_BY_MONTHLY_PAYMENT) {
            return $this->sortOptionsByMonthlyPayment();
        }

        return $this->sortOptionsByTerm();
    }

    /**
     * Returns a "ready to use" array including the information whether or not
     * a rate has to display a star in the template.
     *
     * A star is required for the following conditions:
     *  1. The rate has the highest APR (if they are different at all)
     *  2. The cheapest rate depending on the monthly payment
     *
     * @param int $sortParameter
     *
     * @return array
     */
    public function finalizeList($sortParameter = self::SORT_BY_TERM)
    {
        $data = (array) $this->sortOptionsBy($sortParameter)->toArray()['qualifyingFinancingOptions'];

        if (!$data || count($data) === 0) {
            return $data;
        }

        //Step1 - Check if we have different APR.
        $latestApr = (float) $data[0]['creditFinancing']['apr']; //Initial value for the next step

        //The index of the entry that has the highest APR.
        //This index is being calculated in the first for-loop below
        $highestAprIndex = 0;

        //This boolean indicates if the response has different values as apr.
        $hasDifferentApr = false;

        //We start from 1 because 0 is the default value above (we can save one iteration)
        $iMax = count($data);
        for ($i = 1; $i < $iMax; ++$i) {
            $currentApr = (float) $data[$i]['creditFinancing']['apr'];
            if ($currentApr !== $latestApr) {
                $hasDifferentApr = true;

                //In this step we calculate the index of the highest APR in the financing list.
                if ($currentApr > $latestApr) {
                    $latestApr = $currentApr; //Can be used in the next step has "highest APR"-value
                    $highestAprIndex = $i;
                }
            }
        }

        //Step2 - We have different APRs, therefore
        //the highest APR should get the star.
        if ($hasDifferentApr) {
            $data[$highestAprIndex]['hasStar'] = true;
        }

        //Step3 - Always mark the cheapest monthly rate with a star
        $cheapestRate = (float) $data[0]['monthlyPayment']['value'];
        $cheapestRateIndex = 0;
        for ($i = 1; $i < $iMax; ++$i) {
            $currentRate = (float) $data[$i]['monthlyPayment']['value'];
            if ($currentRate < $cheapestRate) {
                $cheapestRateIndex = $i;
                $cheapestRate = $currentRate;
            }
        }
        $data[$cheapestRateIndex]['hasStar'] = true;

        return $data;
    }

    /**
     * @return FinancingResponse
     */
    private function sortOptionsByMonthlyPayment()
    {
        /** @var FinancingOption[] $options */
        $options = $this->financingResponse->getQualifyingFinancingOptions();

        usort($options, function (&$option1, &$option2) {
            /** @var FinancingOption $option1 */
            /** @var FinancingOption $option2 */
            if ($option1->getMonthlyPayment()->getValue() === $option2->getMonthlyPayment()->getValue()) {
                return 0;
            }

            return $option1->getMonthlyPayment()->getValue() < $option2->getMonthlyPayment()->getValue() ? -1 : 1;
        });

        $this->financingResponse->setQualifyingFinancingOptions($options);

        return $this->financingResponse;
    }

    /**
     * @return FinancingResponse
     */
    private function sortOptionsByTerm()
    {
        /** @var FinancingOption[] $options */
        $options = $this->financingResponse->getQualifyingFinancingOptions();

        usort($options, function (&$option1, &$option2) {
            /** @var FinancingOption $option1 */
            /** @var FinancingOption $option2 */
            if ($option1->getCreditFinancing()->getTerm() === $option2->getCreditFinancing()->getTerm()) {
                return 0;
            }

            return $option1->getCreditFinancing()->getTerm() < $option2->getCreditFinancing()->getTerm() ? -1 : 1;
        });

        $this->financingResponse->setQualifyingFinancingOptions($options);

        return $this->financingResponse;
    }
}
