<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use Shopware_Components_Config;

class CompanyInfoService
{
    /**
     * @var Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @param Shopware_Components_Config $shopwareConfig
     */
    public function __construct(Shopware_Components_Config $shopwareConfig)
    {
        $this->shopwareConfig = $shopwareConfig;
    }

    /**
     *  Returns an array that contains the basic company information of the current shop.
     *
     *  @return array
     */
    public function getCompanyInfo()
    {
        $companyInfo = [];
        $companyInfo['address'] = $this->shopwareConfig->get('address');
        $companyInfo['name'] = $this->shopwareConfig->get('company');

        return $companyInfo;
    }
}
