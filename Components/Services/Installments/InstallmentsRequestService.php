<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingRequest;

class InstallmentsRequestService
{
    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var InstallmentsResource
     */
    private $resource;

    /**
     * @param InstallmentsResource   $resource
     * @param LoggerServiceInterface $logger
     */
    public function __construct(InstallmentsResource $resource, LoggerServiceInterface $logger)
    {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * @param float $productPrice
     *
     * @return array
     */
    public function getList($productPrice)
    {
        //Prepare the request
        $financingRequest = new FinancingRequest();
        $financingRequest->setFinancingCountryCode('DE');
        $transactionAmount = new FinancingRequest\TransactionAmount();
        $transactionAmount->setValue($productPrice);
        $transactionAmount->setCurrencyCode('EUR');
        $financingRequest->setTransactionAmount($transactionAmount);

        try {
            return $this->resource->getFinancingOptions($financingRequest);
        } catch (RequestException $e) {
            $this->logger->error(
                'Could not get installments financing options due to a communication failure',
                [
                    $e->getMessage(),
                    $e->getBody(),
                ]
            );

            return null;
        }
    }
}
