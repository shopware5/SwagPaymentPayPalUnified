<?php declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Backend;

use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Components\Services\TransactionHistoryBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentIntent;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\SaleResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Sale;

class PaymentDetailsService
{
    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var SaleResource
     */
    private $saleResource;

    /**
     * @var LegacyService
     */
    private $legacyService;

    /**
     * @var TransactionHistoryBuilderService
     */
    private $transactionHistoryBuilder;

    public function __construct(
        ExceptionHandlerServiceInterface $exceptionHandler,
        PaymentResource $paymentResource,
        SaleResource $saleResource,
        LegacyService $legacyService,
        TransactionHistoryBuilderService $transactionHistoryBuilder
    ) {
        $this->exceptionHandler = $exceptionHandler;
        $this->paymentResource = $paymentResource;
        $this->saleResource = $saleResource;
        $this->legacyService = $legacyService;
        $this->transactionHistoryBuilder = $transactionHistoryBuilder;
    }

    /**
     * @param string $paymentId
     * @param string $paymentMethodId
     * @param string $transactionId
     *
     * @return array
     */
    public function getPaymentDetails($paymentId, $paymentMethodId, $transactionId)
    {
        $legacyPaymentIds = $this->legacyService->getClassicPaymentIds();

        try {
            //Check for a legacy payment
            if (in_array($paymentMethodId, $legacyPaymentIds, true)) {
                $viewParameter = $this->prepareLegacyDetails($transactionId);
            } else {
                $viewParameter = $this->prepareUnifiedDetails($paymentId);
            }
        } catch (\Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain payment details');

            $viewParameter = [
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ];
        }

        return $viewParameter;
    }

    /**
     * @param string $transactionId
     *
     * @return array
     */
    private function prepareLegacyDetails($transactionId)
    {
        $viewParameter = [
            'success' => true,
            'legacy' => true,
        ];

        $details = $this->saleResource->get($transactionId);
        $details['intent'] = 'sale';

        $viewParameter['history'] = $this->transactionHistoryBuilder->getLegacyHistory(Sale::fromArray($details));
        $viewParameter['payment'] = $details;

        return $viewParameter;
    }

    /**
     * @param string $paymentId
     *
     * @return array
     */
    private function prepareUnifiedDetails($paymentId)
    {
        $viewParameter = [
            'success' => true,
        ];

        $paymentDetails = $this->paymentResource->get($paymentId);
        $viewParameter['payment'] = $paymentDetails;
        $viewParameter['history'] = $this->transactionHistoryBuilder->getTransactionHistory($paymentDetails);

        if ($paymentDetails['intent'] === PaymentIntent::AUTHORIZE) {
            //Separately assign the data, to provide an easier usage
            $viewParameter['authorization'] = $paymentDetails['transactions'][0]['related_resources'][0]['authorization'];
        } elseif ($paymentDetails['intent'] === PaymentIntent::ORDER) {
            $viewParameter['order'] = $paymentDetails['transactions'][0]['related_resources'][0]['order'];
        } elseif ($paymentDetails['intent'] === PaymentIntent::SALE) {
            $viewParameter['sale'] = $paymentDetails['transactions'][0]['related_resources'][0]['sale'];
        }

        return $viewParameter;
    }
}
