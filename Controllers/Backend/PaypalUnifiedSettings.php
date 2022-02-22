<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\Services\ExceptionHandlerService;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

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

            $webhookResource = $this->get('paypal_unified.webhook_resource');
            $webhookResource->create($url, ['*']);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'register webhooks');

            if ($error->getName() === ExceptionHandlerService::WEBHOOK_ALREADY_EXISTS_ERROR) {
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
     * @return void
     */
    public function isCapableAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId', 0);
        $sandbox = (bool) $this->Request()->getParam('sandbox', false);
        $payerId = $this->Request()->getParam('payerId');
        $paymentMethodCapabilityNames = $this->Request()->getParam('paymentMethodCapabilityNames');
        $productSubscriptionNames = $this->Request()->getParam('productSubscriptionNames');

        if ($shopId === 0) {
            $this->view->assign([
                'success' => false,
                'message' => 'The parameter "shopId" is required.',
            ]);

            return;
        }

        if ($payerId === null) {
            $this->view->assign([
                'success' => false,
                'message' => 'The parameter "payerId" is required.',
            ]);

            return;
        }

        if (!\is_array($paymentMethodCapabilityNames)) {
            $this->view->assign([
                'success' => false,
                'message' => 'The parameter "paymentMethodCapabilityNames" should be a array.',
            ]);

            return;
        }

        if (!\is_array($productSubscriptionNames)) {
            $this->view->assign([
                'success' => false,
                'message' => 'The parameter "productSubscriptionNames" should be a array.',
            ]);

            return;
        }

        $onboardingStatusService = $this->container->get('paypal_unified.onboarding_status_service');

        $viewAssign = [];
        try {
            foreach ($paymentMethodCapabilityNames as $paymentMethodCapabilityName) {
                $viewAssign[$paymentMethodCapabilityName] = $onboardingStatusService->isCapable($payerId, $shopId, $sandbox, $paymentMethodCapabilityName);
            }
            foreach ($productSubscriptionNames as $productSubscriptionName) {
                $viewAssign[$productSubscriptionName] = $onboardingStatusService->isSubscribed($payerId, $shopId, $sandbox, $productSubscriptionName);
            }
        } catch (\Exception $exception) {
            $this->exceptionHandler->handle($exception, 'validate capability');

            $this->View()->assign([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);

            return;
        }

        $viewAssign['success'] = true;

        $this->view->assign($viewAssign);
    }

    private function configureClient()
    {
        $request = $this->Request();
        $shopId = (int) $request->getParam('shopId');
        $sandbox = $request->getParam('sandbox', 'false') !== 'false';
        $restId = $request->getParam('clientId');
        $restSecret = $request->getParam('clientSecret');
        $restIdSandbox = $request->getParam('sandboxClientId');
        $restSecretSandbox = $request->getParam('sandboxClientSecret');

        $this->clientService->configure([
            'clientId' => $restId,
            'clientSecret' => $restSecret,
            'sandboxClientId' => $restIdSandbox,
            'sandboxClientSecret' => $restSecretSandbox,
            'sandbox' => $sandbox,
            'shopId' => $shopId,
        ]);
    }
}
