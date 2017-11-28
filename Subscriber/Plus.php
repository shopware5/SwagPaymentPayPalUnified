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

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Models\Shop\DetachedShop;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Plus implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var DetachedShop
     */
    private $shop;

    /**
     * @var array
     */
    private static $allowedActions = ['shippingPayment', 'confirm', 'finish'];

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->settingsService = $container->get('paypal_unified.settings_service');
        $this->logger = $container->get('paypal_unified.logger_service');
        $this->paymentMethodProvider = new PaymentMethodProvider($container->get('models'));

        /** @var DependencyProvider $dependencyProvider */
        $dependencyProvider = $container->get('paypal_unified.dependency_provider');
        $this->shop = $dependencyProvider->getShop();
        $this->snippetManager = $container->get('snippets');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
        ];
    }

    /**
     * Checks the requirements for the payment wall and assigns the data to the view if the payment wall is displayed.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchCheckout(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = $controller->get('session');

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        $action = $request->getActionName();
        $unifiedActive = (bool) $this->settingsService->get('active');
        $usePayPalPlus = (bool) $this->settingsService->get('active', SettingsTable::PLUS);

        $errorCode = $request->getParam('paypal_unified_error_code');
        $errorMessage = $request->getParam('paypal_unified_error_message');
        $errorName = $request->getParam('paypal_unified_error_name');

        if ($unifiedActive && $errorCode) {
            $view->assign('paypalUnifiedErrorCode', $errorCode);
            $view->assign('paypalUnifiedErrorMessage', $errorMessage);
            $view->assign('paypalUnifiedErrorName', $errorName);
        }

        if (!$unifiedActive || !$usePayPalPlus || $controller->Response()->isRedirect()) {
            return;
        }

        if (!in_array($action, $this::$allowedActions, true)) {
            $session->offsetUnset('paypalUnifiedCameFromPaymentSelection');

            return;
        }

        $isUnifiedSelected = false;
        $paymentModel = $this->paymentMethodProvider->getPaymentMethodModel();
        if ($paymentModel) {
            $isUnifiedSelected = $paymentModel->getId() === (int) $view->getAssign('sPayment')['id'];
        }

        $this->overwritePaymentName($view);

        $view->assign('paypalUnifiedUsePlus', $usePayPalPlus);

        if ($action === 'finish' && $isUnifiedSelected) {
            $this->handleFinishDispatch($view);
        } elseif ($action === 'confirm' && $isUnifiedSelected) {
            $this->handleConfirmDispatch($view, $session);
        } elseif ($action === 'shippingPayment') {
            $this->handleShippingPaymentDispatch($view, $session);
        }
    }

    /**
     * Handles the finish dispatch and assigns the payment instructions to the template.
     *
     * @param \Enlight_View_Default $view
     */
    private function handleFinishDispatch(\Enlight_View_Default $view)
    {
        /** @var PaymentInstructionService $instructionService */
        $instructionService = $this->container->get('paypal_unified.payment_instruction_service');

        $orderNumber = $view->getAssign('sOrderNumber');
        $paymentInstructions = $instructionService->getInstructions($orderNumber);

        if ($paymentInstructions) {
            $paymentInstructionsArray = $paymentInstructions->toArray();
            $view->assign('sTransactionumber', $paymentInstructionsArray['reference']);
            $view->assign('paypalUnifiedPaymentInstructions', $paymentInstructionsArray);
            $payment = $view->getAssign('sPayment');
            $payment['description'] = $this->snippetManager->getNamespace('frontend/paypal_unified/checkout/finish')->get('paymentName/PayPalPlusInvoice');
            $view->assign('sPayment', $payment);
        } else {
            /** @var OrderDataService $orderDataService */
            $orderDataService = $this->container->get('paypal_unified.order_data_service');
            $transactionId = $orderDataService->getTransactionId($orderNumber);
            $view->assign('sTransactionumber', $transactionId);
        }
    }

    /**
     * @param \Enlight_View_Default                 $view
     * @param \Enlight_Components_Session_Namespace $session
     */
    private function handleConfirmDispatch(\Enlight_View_Default $view, \Enlight_Components_Session_Namespace $session)
    {
        // Check if the user is coming from checkout step 2 (payment & shipping)
        $cameFromPaymentSelection = $session->get('paypalUnifiedCameFromPaymentSelection', false);

        //This value could be set in the shippingPayment action.
        //If so, the payment does not need to be created again.
        $remotePaymentId = $session->get('paypalUnifiedRemotePaymentId');

        $view->assign('paypalUnifiedCameFromPaymentSelection', $cameFromPaymentSelection);
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection')));
        $view->assign('paypalUnifiedFixedCart', true);

        //If the payment has already been created in the payment selection,
        //we don't have to do anything else.
        if ($cameFromPaymentSelection && $remotePaymentId) {
            $view->assign('paypalUnifiedRemotePaymentId', $remotePaymentId);

            return;
        }

        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        if (!$paymentStruct) {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $this->settingsService->get('sandbox'));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()[1]->getHref());
        $view->assign('paypalUnifiedPlusLanguageIso', $this->getPaymentWallLanguage());
    }

    /**
     * @param \Enlight_View_Default                 $view
     * @param \Enlight_Components_Session_Namespace $session
     */
    private function handleShippingPaymentDispatch(\Enlight_View_Default $view, \Enlight_Components_Session_Namespace $session)
    {
        $restylePaymentSelection = (bool) $this->settingsService->get('restyle', SettingsTable::PLUS);
        $view->assign('paypalUnifiedRestylePaymentSelection', $restylePaymentSelection);

        $session->offsetSet('paypalUnifiedCameFromPaymentSelection', true);
        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        if (!$paymentStruct) {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $this->settingsService->get('sandbox'));
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection')));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()[1]->getHref());
        $view->assign('paypalUnifiedPlusLanguageIso', $this->getPaymentWallLanguage());

        //Store the paymentID in the session to indicate that
        //the payment has already been created and can be used on the confirm page.
        $session->offsetSet('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
    }

    /**
     * @param array $basketData
     * @param array $userData
     *
     * @return Payment|null
     */
    private function createPayment(array $basketData, array $userData)
    {
        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');
        $webProfileId = $this->settingsService->get('web_profile_id');

        /** @var ClientService $client */
        $client = $this->container->get('paypal_unified.client_service');

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setUserData($userData);
        $requestParams->setWebProfileId($webProfileId);
        $requestParams->setBasketData($basketData);
        $requestParams->setPaymentType(PaymentType::PAYPAL_PLUS);

        $params = $this->container->get('paypal_unified.plus.payment_builder_service')->getPayment($requestParams);

        try {
            $client->setPartnerAttributionId(PartnerAttributionId::PAYPAL_PLUS);
            $payment = $paymentResource->create($params);

            return Payment::fromArray($payment);
        } catch (RequestException $ex) {
            $this->logger->error('Could not create payment', ['message' => $ex->getMessage(), 'payload' => $ex->getBody()]);

            return null;
        }
    }

    /**
     * @return string
     */
    private function getPaymentWallLanguage()
    {
        $languageIso = $this->shop->getLocale()->getLocale();

        $plusLanguage = 'en_US';
        // use english as default, use german if the locale is from german speaking country (de_DE, de_AT, etc)
        // by now the PPP iFrame does not support other languages
        if (strpos($languageIso, 'de_') === 0) {
            $plusLanguage = 'de_DE';
        }

        return $plusLanguage;
    }

    /**
     * @param \Enlight_View_Default $view
     */
    private function overwritePaymentName(\Enlight_View_Default $view)
    {
        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId($this->container->get('dbal_connection'));
        $paymentName = $this->settingsService->get('payment_name', SettingsTable::PLUS);
        $paymentDescription = $this->settingsService->get('payment_description', SettingsTable::PLUS);

        if ($paymentName === '' || $paymentName === null) {
            return;
        }

        $customerData = $view->getAssign('sUserData');
        $customerPayment = $customerData['additional']['payment'];
        if ((int) $customerPayment['id'] === $unifiedPaymentId) {
            $customerPayment['description'] = $paymentName;
            $customerPayment['additionaldescription'] .= '<br>' . $paymentDescription;

            $customerData['additional']['payment'] = $customerPayment;
            $view->assign('sUserData', $customerData);
        }

        $paymentMethods = $view->getAssign('sPayments');
        foreach ($paymentMethods as &$paymentMethod) {
            if ((int) $paymentMethod['id'] === $unifiedPaymentId) {
                $paymentMethod['description'] = $paymentName;
                $paymentMethod['additionaldescription'] .= '<br>' . $paymentDescription;
                break;
            }
        }
        unset($paymentMethod);
        $view->assign('sPayments', $paymentMethods);

        $selectedPaymentMethod = $view->getAssign('sPayment');
        if ((int) $selectedPaymentMethod['id'] === $unifiedPaymentId) {
            $selectedPaymentMethod['description'] = $paymentName;
            $selectedPaymentMethod['additionaldescription'] .= '<br>' . $paymentDescription;

            $view->assign('sPayment', $selectedPaymentMethod);
        }
    }
}
