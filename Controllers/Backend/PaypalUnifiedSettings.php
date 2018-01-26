<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

            if ($error->getName() === 'WEBHOOK_URL_ALREADY_EXISTS') {
                $this->View()->assign([
                    'success' => true,
                    'url' => $url,
                ]);

                return;
            }

            $this->View()->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
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
                'message' => $error->getCompleteMessage(),
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
                'message' => $error->getCompleteMessage(),
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
        $shopId = (int) $this->Request()->getParam('shopId');
        $logoImage = $this->Request()->getParam('logoImage');
        $brandName = $this->Request()->getParam('brandName');
        $webProfileId = null;
        $ecWebProfileId = null;
        $error = null;

        $settings = [
            'shopId' => $shopId,
            'logoImage' => $logoImage,
            'brandName' => $brandName,
        ];

        /** @var WebProfileService $webProfileService */
        $webProfileService = $this->get('paypal_unified.web_profile_service');

        try {
            $this->configureClient();

            $webProfileId = $webProfileService->getWebProfile($settings);
            $ecWebProfileId = $webProfileService->getWebProfile($settings, true);
        } catch (Exception $rex) {
            $error = $this->exceptionHandler->handle($rex, 'request the web profiles');
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

        if ($error !== null) {
            $this->View()->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);

            return;
        }

        $this->View()->assign('success', true);
    }

    private function configureClient()
    {
        $request = $this->Request();
        $shopId = (int) $request->getParam('shopId');
        $restId = $request->getParam('clientId');
        $sandbox = $request->getParam('sandbox', 'false') !== 'false';
        $restSecret = $request->getParam('clientSecret');

        $this->clientService->configure([
            'clientId' => $restId,
            'clientSecret' => $restSecret,
            'sandbox' => $sandbox,
            'shopId' => $shopId,
        ]);
    }
}
