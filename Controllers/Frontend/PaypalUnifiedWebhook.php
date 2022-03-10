<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\CSRFWhitelistAware;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\PayPalBundle\Services\WebhookService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;
use SwagPaymentPayPalUnified\WebhookHandlers\AuthorizationVoided;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleComplete;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleDenied;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleRefunded;

class Shopware_Controllers_Frontend_PaypalUnifiedWebhook extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * @var WebhookService
     */
    private $webhookService;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'execute',
        ];
    }

    public function preDispatch()
    {
        $this->webhookService = $this->get('paypal_unified.webhook_service');
        $this->logger = $this->get('paypal_unified.logger_service');

        $paymentStatusService = $this->get('paypal_unified.payment_status_service');

        $this->webhookService->registerWebhooks([
            new SaleComplete($this->logger, $paymentStatusService),
            new SaleDenied($this->logger, $paymentStatusService),
            new SaleRefunded($this->logger, $paymentStatusService),
            new AuthorizationVoided($this->logger, $paymentStatusService),
        ]);
    }

    /**
     * @return void
     */
    public function executeAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        //Get and decode the post data from the request
        $postData = $this->request->getRawBody();
        if (!\is_string($postData)) {
            return;
        }
        $postData = \json_decode($postData, true);

        $this->logger->notify('[Webhook] Received webhook', ['payload' => $postData]);

        if ($postData === null) {
            return;
        }

        try {
            $webhook = Webhook::fromArray($postData);

            //Webhook handler exists?
            if (!$this->webhookService->handlerExists($webhook->getEventType())) {
                $this->logger->warning(
                    '[Webhook] Could not process the request, because no handler has been referenced to this type of event.',
                    [$postData]
                );

                return;
            }

            //Delegate the request to the referenced webhook-handler.
            $this->webhookService->getWebhookHandler($webhook->getEventType())->invoke($webhook);
        } catch (WebhookException $webhookException) {
            $this->logger->error('[Webhhok] ' . $webhookException->getMessage(), ['type' => $webhookException->getEventType()]);
        }
    }
}
