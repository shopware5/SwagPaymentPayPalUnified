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

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse\FinancingOption;

class FinancingOptionsHandler
{
    const SORT_BY_TERM = 0;
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
     * @return FinancingResponse
     */
    private function sortOptionsByMonthlyPayment()
    {
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
