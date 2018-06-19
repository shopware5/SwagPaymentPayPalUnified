<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Models\Shop\DetachedShop;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\OrderDataService;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class Plus implements SubscriberInterface
{
    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var DetachedShop
     */
    private $shop;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentInstructionService
     */
    private $paymentInstructionService;

    /**
     * @var OrderDataService
     */
    private $orderDataService;

    /**
     * @var PaymentBuilderInterface
     */
    private $paymentBuilderService;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandlerService;

    /**
     * @var array
     */
    private static $allowedActions = ['shippingPayment', 'confirm', 'finish'];

    /**
     * @param SettingsServiceInterface         $settingsService
     * @param DependencyProvider               $dependencyProvider
     * @param SnippetManager                   $snippetManager
     * @param Connection                       $connection
     * @param PaymentInstructionService        $paymentInstructionService
     * @param OrderDataService                 $orderDataService
     * @param PaymentBuilderInterface          $paymentBuilderService
     * @param ClientService                    $clientService
     * @param PaymentResource                  $paymentResource
     * @param ExceptionHandlerServiceInterface $exceptionHandlerService
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider,
        SnippetManager $snippetManager,
        Connection $connection,
        PaymentInstructionService $paymentInstructionService,
        OrderDataService $orderDataService,
        PaymentBuilderInterface $paymentBuilderService,
        ClientService $clientService,
        PaymentResource $paymentResource,
        ExceptionHandlerServiceInterface $exceptionHandlerService
    ) {
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->settingsService = $settingsService;
        $this->shop = $dependencyProvider->getShop();
        $this->snippetManager = $snippetManager;
        $this->connection = $connection;
        $this->paymentInstructionService = $paymentInstructionService;
        $this->orderDataService = $orderDataService;
        $this->paymentBuilderService = $paymentBuilderService;
        $this->clientService = $clientService;
        $this->paymentResource = $paymentResource;
        $this->exceptionHandlerService = $exceptionHandlerService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter' => 'addPaymentMethodsAttributes',
        ];
    }

    /**
     * Checks the requirements for the payment wall and assigns the data to the view if the payment wall is displayed.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchCheckout(\Enlight_Controller_ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        $unifiedActive = (bool) $this->settingsService->get('active');
        if (!$unifiedActive) {
            return;
        }

        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        /** @var \Enlight_Components_Session_Namespace $session */
        $session = $controller->get('session');

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        $errorCode = $request->getParam('paypal_unified_error_code');
        $errorName = $request->getParam('paypal_unified_error_name');
        $errorMessage = $request->getParam('paypal_unified_error_message');

        if ($errorCode) {
            // all integrations
            $view->assign('paypalUnifiedErrorCode', $errorCode);
            $view->assign('paypalUnifiedErrorName', $errorName);
            $view->assign('paypalUnifiedErrorMessage', $errorMessage);
        }

        $isExpressCheckout = (bool) $request->getParam('expressCheckout', false);
        if ($isExpressCheckout) {
            return;
        }

        $usePayPalPlus = (bool) $this->settingsService->get('active', SettingsTable::PLUS);
        if (!$usePayPalPlus || $controller->Response()->isRedirect()) {
            return;
        }

        $action = $request->getActionName();
        if (!in_array($action, $this::$allowedActions, true)) {
            $session->offsetUnset('paypalUnifiedCameFromPaymentSelection');

            return;
        }

        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId($this->connection);
        $isUnifiedSelected = $unifiedPaymentId === (int) $view->getAssign('sPayment')['id'];

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
     * @param \Enlight_Event_EventArgs $args
     *
     * @return array
     */
    public function addPaymentMethodsAttributes(\Enlight_Event_EventArgs $args)
    {
        $paymentMethods = $args->getReturn();

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return $paymentMethods;
        }

        $unifiedActive = (bool) $this->settingsService->get('active');
        if (!$unifiedActive) {
            return $paymentMethods;
        }

        $usePayPalPlus = (bool) $this->settingsService->get('active', SettingsTable::PLUS);
        if (!$usePayPalPlus) {
            return $paymentMethods;
        }

        $integrateThirdPartyMethods = (bool) $this->settingsService->get('integrate_third_party_methods', SettingsTable::PLUS);
        if (!$integrateThirdPartyMethods) {
            return $paymentMethods;
        }

        $paymentIds = array_column($paymentMethods, 'id');

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select(
            'paymentmeanID',
            'swag_paypal_unified_display_in_plus_iframe',
            'swag_paypal_unified_plus_iframe_payment_logo'
        )
            ->from('s_core_paymentmeans_attributes')
            ->where('paymentmeanID IN(:paymentIds)')
            ->setParameter('paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY);

        $attributes = $queryBuilder->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        foreach ($paymentMethods as &$paymentMethod) {
            if (array_key_exists($paymentMethod['id'], $attributes)) {
                $attribute = $attributes[$paymentMethod['id']];
                $paymentMethod['swag_paypal_unified_display_in_plus_iframe'] = (bool) $attribute['swag_paypal_unified_display_in_plus_iframe'];
                $paymentMethod['swag_paypal_unified_plus_iframe_payment_logo'] = $attribute['swag_paypal_unified_plus_iframe_payment_logo'];
            }
        }
        unset($paymentMethod);

        return $paymentMethods;
    }

    /**
     * Handles the finish dispatch and assigns the payment instructions to the template.
     *
     * @param \Enlight_View_Default $view
     */
    private function handleFinishDispatch(\Enlight_View_Default $view)
    {
        $orderNumber = $view->getAssign('sOrderNumber');
        $paymentInstructions = $this->paymentInstructionService->getInstructions($orderNumber);

        if ($paymentInstructions) {
            $paymentInstructionsArray = $paymentInstructions->toArray();
            $view->assign('sTransactionumber', $paymentInstructionsArray['reference']);
            $view->assign('paypalUnifiedPaymentInstructions', $paymentInstructionsArray);
            $payment = $view->getAssign('sPayment');
            $payment['description'] = $this->snippetManager->getNamespace('frontend/paypal_unified/checkout/finish')->get('paymentName/PayPalPlusInvoice');
            $view->assign('sPayment', $payment);
        } else {
            $transactionId = $this->orderDataService->getTransactionId($orderNumber);
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
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->connection));

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
        $session->offsetSet('paypalUnifiedCameFromPaymentSelection', true);
        $paymentStruct = $this->createPayment($view->getAssign('sBasket'), $view->getAssign('sUserData'));

        if (!$paymentStruct) {
            return;
        }

        $view->assign('paypalUnifiedModeSandbox', $this->settingsService->get('sandbox'));
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->connection));
        $view->assign('paypalUnifiedRemotePaymentId', $paymentStruct->getId());
        $view->assign('paypalUnifiedApprovalUrl', $paymentStruct->getLinks()[1]->getHref());
        $view->assign('paypalUnifiedPlusLanguageIso', $this->getPaymentWallLanguage());

        //Store the paymentID in the session to indicate that
        //the payment has already been created and can be used on the confirm page.
        $session->offsetSet('paypalUnifiedRemotePaymentId', $paymentStruct->getId());

        $restylePaymentSelection = (bool) $this->settingsService->get('restyle', SettingsTable::PLUS);
        $view->assign('paypalUnifiedRestylePaymentSelection', $restylePaymentSelection);

        $integrateThirdPartyMethods = (bool) $this->settingsService->get('integrate_third_party_methods', SettingsTable::PLUS);
        if (!$integrateThirdPartyMethods) {
            return;
        }

        $this->handleIntegratingThirdPartyMethods($view);
    }

    /**
     * @param array $basketData
     * @param array $userData
     *
     * @return Payment|null
     */
    private function createPayment(array $basketData, array $userData)
    {
        $webProfileId = $this->settingsService->get('web_profile_id');

        $requestParams = new PaymentBuilderParameters();
        $requestParams->setUserData($userData);
        $requestParams->setWebProfileId($webProfileId);
        $requestParams->setBasketData($basketData);
        $requestParams->setPaymentType(PaymentType::PAYPAL_PLUS);

        $params = $this->paymentBuilderService->getPayment($requestParams);

        try {
            $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_PLUS);
            $payment = $this->paymentResource->create($params);

            return Payment::fromArray($payment);
        } catch (\Exception $ex) {
            $this->exceptionHandlerService->handle($ex, 'create payment for plus payment wall');

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
        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId($this->connection);
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

    /**
     * @param \Enlight_View_Default $view
     */
    private function handleIntegratingThirdPartyMethods(\Enlight_View_Default $view)
    {
        $paymentMethods = $view->getAssign('sPayments');
        $paymentMethodsForPaymentWall = [];
        foreach ($paymentMethods as $key => $paymentMethod) {
            if ($paymentMethod['name'] === PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME) {
                continue;
            }

            if (!array_key_exists('swag_paypal_unified_display_in_plus_iframe', $paymentMethod)) {
                continue;
            }

            if ($paymentMethod['swag_paypal_unified_display_in_plus_iframe']) {
                $paymentMethodsForPaymentWall[] = [
                    'redirectUrl' => 'http://' . $paymentMethod['id'],
                    // 25 is the max length for payment name
                    // cut here, because the name is needed for a check in jQuery plugin
                    'methodName' => substr($paymentMethod['description'], 0, 25),
                    'description' => $paymentMethod['additionaldescription'],
                    'imageUrl' => $paymentMethod['swag_paypal_unified_plus_iframe_payment_logo'],
                ];
            }
        }

        $view->assign('paypalUnifiedPlusPaymentMethodsPaymentWall', json_encode($paymentMethodsForPaymentWall));
    }
}
