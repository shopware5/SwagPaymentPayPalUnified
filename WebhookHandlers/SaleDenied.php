<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\WebhookHandlers;

use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\Exception\OrderNotFoundException;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookEventTypes;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookHandler;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

class SaleDenied implements WebhookHandler
{
    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var PaymentStatusService
     */
    private $paymentStatusService;

    public function __construct(LoggerServiceInterface $logger, PaymentStatusService $paymentStatusService)
    {
        $this->logger = $logger;
        $this->paymentStatusService = $paymentStatusService;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType()
    {
        return WebhookEventTypes::PAYMENT_SALE_DENIED;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(Webhook $webhook)
    {
        $parentPayment = $webhook->getResource()['parent_payment'];
        try {
            $this->paymentStatusService->updatePaymentStatus(
                $parentPayment,
                Status::PAYMENT_STATE_OPEN
            );

            return true;
        } catch (OrderNotFoundException $e) {
            $this->logger->error(
                '[SaleDenied-Webhook] Could not find associated order with the temporaryID ' . $parentPayment,
                ['webhook' => $webhook->toArray()]
            );

            return false;
        } catch (\Exception $ex) {
            $this->logger->error(
                '[SaleDenied-Webhook] Could not update entity',
                ['message' => $ex->getMessage(), 'stacktrace' => $ex->getTrace()]
            );

            return false;
        }
    }
}
