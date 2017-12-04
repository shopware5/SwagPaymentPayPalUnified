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

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->webhookService = $this->get('paypal_unified.webhook_service');
        $this->logger = $this->get('paypal_unified.logger_service');

        $this->webhookService->registerWebhooks([
            new SaleComplete($this->logger, $this->get('models')),
            new SaleDenied($this->logger, $this->get('models')),
            new SaleRefunded($this->logger, $this->get('models')),
            new AuthorizationVoided($this->logger, $this->get('models')),
        ]);
    }

    public function executeAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        //Get and decode the post data from the request
        $postData = $this->request->getRawBody();
        $postData = json_decode($postData, true);

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
