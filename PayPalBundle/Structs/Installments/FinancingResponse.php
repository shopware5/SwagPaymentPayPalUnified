<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
