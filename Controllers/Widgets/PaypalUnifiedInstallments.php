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

use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\Components\Services\Installments\FinancingOptionsHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;
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

    public function modalContentAction()
    {
        $productPrice = $this->Request()->getParam('productPrice');

        $financingRequest = new FinancingRequest();
        $financingRequest->setFinancingCountryCode('DE');
        $transactionAmount = new FinancingRequest\TransactionAmount();
        $transactionAmount->setValue($productPrice);
        $transactionAmount->setCurrencyCode('EUR');
        $financingRequest->setTransactionAmount($transactionAmount);

        try {
            $response = $this->installmentsResource->getFinancingOptions($financingRequest);
        } catch (RequestException $e) {
            $this->pluginLogger->error(
                'PayPal Unified: Could not get installments financing options due to a communication failure',
                [
                    $e->getMessage(),
                    $e->getBody(),
                ]
            );

            // TODO: error ins modal

            return;
        }

        if (!isset($response['financing_options'][0])) {
            $this->pluginLogger->error('PayPal Unified: Could not find financing options in response');
            // TODO: error ins modal

            return;
        }

        $financingResponseStruct = FinancingResponse::fromArray($response['financing_options'][0]);

        $optionsHandler = new FinancingOptionsHandler($financingResponseStruct);

        $financingResponseStruct = $optionsHandler->sortOptionsBy(FinancingOptionsHandler::SORT_BY_TERM);
        echo '<pre>';
        print_r($financingResponseStruct);
        echo '</pre>';
        exit();

        $qualifyingFinancingOptions = $financingResponseStruct->toArray()['qualifyingFinancingOptions'];

        $this->View()->assign('payPalUnifiedInstallmentsFinancingOptions', $qualifyingFinancingOptions);
        $this->View()->assign('payPalUnifiedInstallmentsProductPrice', $productPrice);
    }
}
