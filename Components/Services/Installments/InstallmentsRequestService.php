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

use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Logger;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;

class InstallmentsRequestService
{
    /**
     * @var Logger
     */
    private $pluginLogger;

    /**
     * @var InstallmentsResource
     */
    private $resource;

    /**
     * @param InstallmentsResource $resource
     * @param Logger               $logger
     */
    public function __construct(InstallmentsResource $resource, Logger $logger)
    {
        $this->resource = $resource;
        $this->pluginLogger = $logger;
    }

    /**
     * @param float $productPrice
     * @param bool  $serialize
     *
     * @return array|null|FinancingResponse
     */
    public function getList($productPrice, $serialize = false)
    {
        //Prepare the request
        $financingRequest = new FinancingRequest();
        $financingRequest->setFinancingCountryCode('DE');
        $transactionAmount = new FinancingRequest\TransactionAmount();
        $transactionAmount->setValue($productPrice);
        $transactionAmount->setCurrencyCode('EUR');
        $financingRequest->setTransactionAmount($transactionAmount);

        try {
            $response = $this->resource->getFinancingOptions($financingRequest);

            return $serialize ? FinancingResponse::fromArray($response) : $response;
        } catch (RequestException $e) {
            $this->pluginLogger->error(
                'PayPal Unified: Could not get installments financing options due to a communication failure',
                [
                    $e->getMessage(),
                    $e->getBody(),
                ]
            );

            return null;
        }
    }
}
