<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Common;

use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

abstract class Address extends PayPalApiStruct
{
    /**
     * The first line of the address. For example, number or street. For example, 173 Drury Lane.
     * Required for data entry and compliance and risk checks. Must contain the full address.
     *
     * @var string|null
     */
    protected $addressLine_1;

    /**
     * The second line of the address. For example, suite or apartment number.
     *
     * @var string|null
     */
    protected $addressLine_2;

    /**
     * A city, town, or village. Smaller than $adminArea1
     *
     * @var string|null
     */
    protected $adminArea_2;

    /**
     * The highest level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
     * Format for postal delivery. For example, CA and not California.
     *
     * @var string|null
     */
    protected $adminArea_1;

    /**
     * @var string|null
     */
    protected $postalCode;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @return string|null
     */
    public function getAddressLine1()
    {
        return $this->addressLine_1;
    }

    /**
     * @param string|null $addressLine_1
     *
     * @return void
     */
    public function setAddressLine1($addressLine_1)
    {
        $this->addressLine_1 = $addressLine_1;
    }

    /**
     * @return string|null
     */
    public function getAddressLine2()
    {
        return $this->addressLine_2;
    }

    /**
     * @param string|null $addressLine_2
     *
     * @return void
     */
    public function setAddressLine2($addressLine_2)
    {
        $this->addressLine_2 = $addressLine_2;
    }

    /**
     * @return string|null
     */
    public function getAdminArea2()
    {
        return $this->adminArea_2;
    }

    /**
     * @param string|null $adminArea_2
     *
     * @return void
     */
    public function setAdminArea2($adminArea_2)
    {
        $this->adminArea_2 = $adminArea_2;
    }

    /**
     * @return string|null
     */
    public function getAdminArea1()
    {
        return $this->adminArea_1;
    }

    /**
     * @param string|null $adminArea_1
     *
     * @return void
     */
    public function setAdminArea1($adminArea_1)
    {
        $this->adminArea_1 = $adminArea_1;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string|null $postalCode
     *
     * @return void
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return void
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }
}
