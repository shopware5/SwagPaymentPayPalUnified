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

use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\Components\Installments\FinancingOptionsHandler;
use SwagPaymentPayPalUnified\Components\Services\Installments\CompanyInfoService;
use SwagPaymentPayPalUnified\Components\Services\Installments\InstallmentsRequestService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;

class Shopware_Controllers_Widgets_PaypalUnifiedInstallments extends Enlight_Controller_Action
{
    /**
     * @var Logger
     */
    private $pluginLogger;

    /**
     * @var InstallmentsResource
     */
    private $installmentsResource;

    public function preDispatch()
    {
        $this->pluginLogger = $this->get('pluginlogger');
        $this->installmentsResource = $this->get('paypal_unified.installments_resource');
    }

    public function cheapestRateAction()
    {
        $productPrice = $this->Request()->get('productPrice');
        $pageType = $this->Request()->get('pageType');

        /** @var InstallmentsRequestService $requestService */
        $requestService = $this->container->get('paypal_unified.installments.installments_request_service');
        $response = $requestService->getList($productPrice);

        if (!isset($response['financing_options'][0])) {
            $this->pluginLogger->error('PayPal Unified: Could not find financing options in response', ['product price' => $productPrice]);

            return;
        }

        //We have to sort the result to get the cheapest rate, since it's being delivered unsorted from paypal
        $financingResponseStruct = FinancingResponse::fromArray($response['financing_options'][0]);
        $optionsHandler = new FinancingOptionsHandler($financingResponseStruct);
        $financingResponseStruct = $optionsHandler->sortOptionsBy(FinancingOptionsHandler::SORT_BY_MONTHLY_PAYMENT);
        $qualifyingFinancingOptions = $financingResponseStruct->toArray()['qualifyingFinancingOptions'];

        /** @var CompanyInfoService $companyInfoService */
        $companyInfoService = $this->container->get('paypal_unified.installments.company_info_service');

        //The cheapest rate is now the first entry in the struct.
        $this->View()->assign('paypalInstallmentsOption', $qualifyingFinancingOptions[0]);
        $this->View()->assign('paypalInstallmentsProductPrice', $productPrice);
        $this->View()->assign('paypalInstallmentsCompanyInfo', $companyInfoService->getCompanyInfo());

        //Depending on this value either the detail or the cart upstream presentment will be loaded
        $this->View()->assign('paypalInstallmentsPageType', $pageType);
    }

    public function listAction()
    {
        $productPrice = $this->Request()->get('productPrice');

        /** @var InstallmentsRequestService $requestService */
        $requestService = $this->container->get('paypal_unified.installments.installments_request_service');
        $response = $requestService->getList($productPrice);

        if (!isset($response['financing_options'][0])) {
            $this->pluginLogger->error('PayPal Unified: Could not find financing options in response', ['product price' => $productPrice]);

            return;
        }

        $financingResponseStruct = FinancingResponse::fromArray($response['financing_options'][0]);
        $optionsHandler = new FinancingOptionsHandler($financingResponseStruct);
        $financingResponseStruct = $optionsHandler->sortOptionsBy(FinancingOptionsHandler::SORT_BY_TERM);
        $qualifyingFinancingOptions = $financingResponseStruct->toArray()['qualifyingFinancingOptions'];

        /** @var CompanyInfoService $companyInfoService */
        $companyInfoService = $this->container->get('paypal_unified.installments.company_info_service');

        $this->View()->assign('paypalInstallmentsOptions', $qualifyingFinancingOptions);
        $this->View()->assign('paypalInstallmentsProductPrice', $productPrice);
        $this->View()->assign('paypalInstallmentsCompanyInfo', $companyInfoService->getCompanyInfo());
    }

    public function modalContentAction()
    {
        $productPrice = $this->Request()->getParam('productPrice');

        /** @var InstallmentsRequestService $requestService */
        $requestService = $this->container->get('paypal_unified.installments.installments_request_service');
        $response = $requestService->getList($productPrice);

        if (!isset($response['financing_options'][0])) {
            $this->pluginLogger->error('PayPal Unified: Could not find financing options in response');
            // TODO: error ins modal

            return;
        }

        $financingResponseStruct = FinancingResponse::fromArray($response['financing_options'][0]);

        $optionsHandler = new FinancingOptionsHandler($financingResponseStruct);

        $financingResponseStruct = $optionsHandler->sortOptionsBy(FinancingOptionsHandler::SORT_BY_TERM);

        $qualifyingFinancingOptions = $financingResponseStruct->toArray()['qualifyingFinancingOptions'];

        $this->View()->assign('payPalUnifiedInstallmentsFinancingOptions', $qualifyingFinancingOptions);
        $this->View()->assign('payPalUnifiedInstallmentsProductPrice', $productPrice);
    }
}
