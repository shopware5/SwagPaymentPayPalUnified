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

use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\WebhookResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Services\WebProfileService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;

class Shopware_Controllers_Backend_PaypalUnifiedSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = GeneralSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'settings';

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandler;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->settingsService = $this->get('paypal_unified.settings_service');
        $this->clientService = $this->get('paypal_unified.client_service');
        $this->exceptionHandler = $this->get('paypal_unified.exception_handler_service');

        parent::preDispatch();
    }

    /**
     * This action handles the register webhook request.
     * It configures the RestClient to the provided credentials and announces
     * a wildcard webhook to the PayPal API.
     */
    public function registerWebhookAction()
    {
        //Generate URL
        /** @var Enlight_Controller_Router $router */
        $router = $this->get('front')->Router();
        $url = $router->assemble([
            'module' => 'frontend',
            'controller' => 'PaypalUnifiedWebhook',
            'action' => 'execute',
            'forceSecure' => 1,
        ]);
        $url = str_replace('http://', 'https://', $url);

        try {
            $this->configureClient();

            $webhookResource = new WebhookResource($this->clientService);
            $webhookResource->create($url, ['*']);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'register webhooks');

            if ($error['message'] === 'Webhook URL already exists') {
                $this->View()->assign([
                    'success' => true,
                    'url' => $url,
                ]);

                return;
            }

            $this->View()->assign([
                'success' => false,
                $error->getCompleteMessage(),
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'url' => $url,
        ]);
    }

    /**
     * Initialize the REST api client to check if the credentials are correct
     */
    public function validateAPIAction()
    {
        try {
            $this->configureClient();
            $this->View()->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'validate API credentials');

            $this->View()->assign([
                'success' => false,
                $error->getCompleteMessage(),
            ]);
        }
    }

    /**
     * Makes a test request against the installments endpoint to test if the installments integration is available
     */
    public function testInstallmentsAvailabilityAction()
    {
        $installmentsRequestService = $this->get('paypal_unified.installments.installments_request_service');

        try {
            $this->configureClient();
            $response = $installmentsRequestService->getList(200.0);
            $financingResponse = FinancingResponse::fromArray($response['financing_options'][0]);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'get installments financing options');

            $this->View()->assign([
                'success' => false,
                $error->getCompleteMessage(),
            ]);

            return;
        }

        if ($financingResponse->getQualifyingFinancingOptions()) {
            $this->View()->assign('success', true);

            return;
        }

        $this->View()->assign('success', false);
    }

    public function createWebProfilesAction()
    {
        try {
            $this->configureClient();
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'configure client for creating webProfiles');

            $this->View()->assign([
                'success' => false,
                $error->getCompleteMessage(),
            ]);

            return;
        }

        $shopId = (int) $this->Request()->getParam('shopId');
        $logoImage = $this->Request()->getParam('logoImage');
        $brandName = $this->Request()->getParam('brandName');

        $settings = [
            'shopId' => $shopId,
            'logoImage' => $logoImage,
            'brandName' => $brandName,
        ];

        /** @var WebProfileService $webProfileService */
        $webProfileService = $this->get('paypal_unified.web_profile_service');
        $webProfileId = $webProfileService->getWebProfile($settings);
        $ecWebProfileId = $webProfileService->getWebProfile($settings, true);

        if ($webProfileId === null || $ecWebProfileId === null) {
            $this->View()->assign('success', false);

            return;
        }

        /** @var ModelManager $entityManager */
        $entityManager = $this->get('models');

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings($shopId);
        if ($generalSettings !== null) {
            $generalSettings->setWebProfileId($webProfileId);
        }

        /** @var ExpressSettingsModel $ecSettings */
        $ecSettings = $this->settingsService->getSettings($shopId, SettingsTable::EXPRESS_CHECKOUT);
        if ($ecSettings !== null) {
            $ecSettings->setWebProfileId($ecWebProfileId);
        }

        $entityManager->flush();
    }

    private function configureClient()
    {
        $shopId = (int) $this->Request()->getParam('shopId');
        $restId = $this->Request()->getParam('clientId');
        $sandbox = (bool) $this->Request()->getParam('sandbox', false);
        $restSecret = $this->Request()->getParam('clientSecret');

        $this->clientService->configure([
            'clientId' => $restId,
            'clientSecret' => $restSecret,
            'sandbox' => $sandbox,
            'shopId' => $shopId,
        ]);
    }
}
