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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse\FinancingOption;

class FinancingResponse
{
    /**
     * @var FinancingOption[]
     */
    private $qualifyingFinancingOptions;

    /**
     * @var FinancingOption[]
     */
    private $nonQualifyingFinancingOptions;

    /**
     * @return FinancingOption[]
     */
    public function getQualifyingFinancingOptions()
    {
        return $this->qualifyingFinancingOptions;
    }

    /**
     * @param FinancingOption[] $qualifyingFinancingOptions
     */
    public function setQualifyingFinancingOptions($qualifyingFinancingOptions)
    {
        $this->qualifyingFinancingOptions = $qualifyingFinancingOptions;
    }

    /**
     * @return FinancingOption[]
     */
    public function getNonQualifyingFinancingOptions()
    {
        return $this->nonQualifyingFinancingOptions;
    }

    /**
     * @param FinancingOption[] $nonQualifyingFinancingOptions
     */
    public function setNonQualifyingFinancingOptions($nonQualifyingFinancingOptions)
    {
        $this->nonQualifyingFinancingOptions = $nonQualifyingFinancingOptions;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $qualifyingFinancingOptions = [];
        foreach ($this->qualifyingFinancingOptions as $qualifyingFinancingOption) {
            $qualifyingFinancingOptions[] = $qualifyingFinancingOption->toArray();
        }

        $nonQualifyingFinancingOptions = [];
        foreach ($this->nonQualifyingFinancingOptions as $nonQualifyingFinancingOption) {
            $nonQualifyingFinancingOptions[] = $nonQualifyingFinancingOption->toArray();
        }

        return [
            'qualifyingFinancingOptions' => $qualifyingFinancingOptions,
            'nonQualifyingFinancingOptions' => $nonQualifyingFinancingOptions,
        ];
    }

    /**
     * @param array $data
     *
     * @return FinancingResponse
     */
    public static function fromArray(array $data = [])
    {
        $financingResponse = new self();

        $qualifyingFinancingOptions = [];
        foreach ($data['qualifying_financing_options'] as $qualifyingFinancingOption) {
            $qualifyingFinancingOption = FinancingOption::fromArray($qualifyingFinancingOption);
            $qualifyingFinancingOptions[] = $qualifyingFinancingOption;
        }
        $financingResponse->setQualifyingFinancingOptions($qualifyingFinancingOptions);

        $nonQualifyingFinancingOptions = [];
        foreach ($data['non_qualifying_financing_options'] as $nonQualifyingFinancingOption) {
            $nonQualifyingFinancingOption = FinancingOption::fromArray($nonQualifyingFinancingOption);
            $nonQualifyingFinancingOptions[] = $nonQualifyingFinancingOption;
        }
        $financingResponse->setNonQualifyingFinancingOptions($nonQualifyingFinancingOptions);

        return $financingResponse;
    }
}
