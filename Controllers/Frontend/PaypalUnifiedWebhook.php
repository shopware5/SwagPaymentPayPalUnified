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
use SwagPaymentPayPalUnified\SDK\Components\Webhook\WebhookException;
use SwagPaymentPayPalUnified\SDK\Services\WebhookGuardService;
use SwagPaymentPayPalUnified\SDK\Services\WebhookService;
use SwagPaymentPayPalUnified\SDK\Structs\Webhook;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleComplete;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleDenied;
use SwagPaymentPayPalUnified\WebhookHandlers\SaleRefunded;

class Shopware_Controllers_Frontend_PaypalUnifiedWebhook extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /** @var WebhookService $webhookService */
    private $webhookService;

    /** @var WebhookGuardService $webhookGuardService */
    private $webhookGuardService;

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'execute'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->webhookService = $this->get('paypal_unified.webhook_service');
        $this->webhookGuardService = $this->get('paypal_unified.webhook_guard_service');

        $this->webhookService->registerWebhooks([
            new SaleComplete($this->get('pluginlogger'), $this->get('models')),
            new SaleDenied($this->get('pluginlogger'), $this->get('models')),
            new SaleRefunded($this->get('pluginlogger'), $this->get('models'))
        ]);
    }

    public function executeAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        //Get and decode the post data from the request
        $postData = $this->request->getRawBody();
        $postData = json_decode($postData, true);

        if ($postData === null) {
            return;
        }

        try {
            $sandboxEnabled = (bool) $this->get('config')->getByNamespace('SwagPaymentPayPalUnified', 'enableSandbox');
            $webhook = Webhook::fromArray($postData);

            //Webhook handler exists?
            if (!$this->webhookService->handlerExists($webhook->getEventType())) {
                $this->get('pluginlogger')->error(
                    'Webhook: Could not process the request, because no handler has been referenced to this type of event.',
                    [ $postData ]
                );

                return;
            }

            //Webhook is allowed?
            if (!$sandboxEnabled && !$this->webhookGuardService->isValid($webhook)) {
                $this->get('pluginlogger')->error(
                    'WebhookGuard: Blocked webhook request, because it could not be verified.',
                    [ $postData ]
                );

                return;
            }

            //Delegate the request to the referenced webhook-handler.
            $this->webhookService->getWebhookHandler($webhook->getEventType())->invoke($webhook);
        } catch (WebhookException $webhookException) {
            $this->get('pluginlogger')->error($webhookException->getMessage(), [ $webhookException->getEventType() ]);
        }
    }
}
