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
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\PaymentAddressService;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAddressPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\Patches\PaymentAmountPatch;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PartnerAttributionId;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Services\ClientService;

class ExpressCheckout implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * @var PaymentAddressService
     */
    private $paymentAddressService;

    /**
     * @var PaymentBuilderInterface
     */
    private $paymentBuilder;

    /**
     * @var ExceptionHandlerServiceInterface
     */
    private $exceptionHandlerService;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @param SettingsServiceInterface         $settingsService
     * @param Session                          $session
     * @param PaymentResource                  $paymentResource
     * @param PaymentAddressService            $addressRequestService
     * @param PaymentBuilderInterface          $paymentBuilder
     * @param ExceptionHandlerServiceInterface $exceptionHandlerService
     * @param Connection                       $connection
     * @param ClientService                    $clientService
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        Session $session,
        PaymentResource $paymentResource,
        PaymentAddressService $addressRequestService,
        PaymentBuilderInterface $paymentBuilder,
        ExceptionHandlerServiceInterface $exceptionHandlerService,
        Connection $connection,
        ClientService $clientService
    ) {
        $this->settingsService = $settingsService;
        $this->session = $session;
        $this->paymentResource = $paymentResource;
        $this->paymentAddressService = $addressRequestService;
        $this->paymentBuilder = $paymentBuilder;
        $this->exceptionHandlerService = $exceptionHandlerService;
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->connection = $connection;
        $this->clientService = $clientService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addExpressCheckoutButtonCart'],
                ['addEcInfoOnConfirm'],
                ['addPaymentInfoToRequest', 100],
            ],
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'addExpressCheckoutButtonDetail',
            'Enlight_Controller_Action_PostDispatch_Frontend_Register' => 'addExpressCheckoutButtonLogin', // cannot use "secure" here, because it's forwarded call from checkout/confirm
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonCart(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getCartActive()) {
            return;
        }

        $action = $args->getRequest()->getActionName();
        $view = $args->getSubject()->View();
        if ($action !== 'cart' && $action !== 'ajaxCart' && $action !== 'ajax_add_article') {
            return;
        }

        $cart = $view->getAssign('sBasket');
        $product = $view->getAssign('sArticle'); // content on modal window of ajaxAddArticleAction

        if ((isset($cart['content']) || $product) && !$view->getAssign('sUserLoggedIn')) {
            $view->assign('paypalUnifiedEcCartActive', true);
            $view->assign('paypalUnifiedModeSandbox', $generalSettings->getSandbox());
            $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
            $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
            $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
            $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addEcInfoOnConfirm(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if ($request->getActionName() === 'confirm' && $request->getParam('expressCheckout', false)) {
            $view->assign('paypalUnifiedExpressCheckout', true);
            $view->assign('paypalUnifiedExpressPaymentId', $request->getParam('paymentId'));
            $view->assign('paypalUnifiedExpressPayerId', $request->getParam('payerId'));
            $view->assign('paypalUnifiedExpressBasketId', $request->getParam('basketId'));
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addPaymentInfoToRequest(ActionEventArgs $args)
    {
        $request = $args->getRequest();

        if ($request->getActionName() === 'payment' &&
            $request->getParam('expressCheckout') &&
            $args->getResponse()->isRedirect()
        ) {
            $paymentId = $request->getParam('paymentId');

            $this->patchAddressAndAmount($paymentId);

            $args->getSubject()->redirect([
                'controller' => 'PaypalUnified',
                'action' => 'return',
                'expressCheckout' => true,
                'paymentId' => $paymentId,
                'PayerID' => $request->getParam('payerId'),
                'basketId' => $request->getParam('basketId'),
            ]);
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonDetail(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getDetailActive()) {
            return;
        }

        $view = $args->getSubject()->View();

        if (!$view->getAssign('userLoggedIn')) {
            $view->assign('paypalUnifiedEcDetailActive', true);
            $view->assign('paypalUnifiedModeSandbox', $generalSettings->getSandbox());
            $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
            $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
            $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
            $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        }
    }

    /**
     * @param ActionEventArgs $args
     */
    public function addExpressCheckoutButtonLogin(ActionEventArgs $args)
    {
        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        /** @var GeneralSettingsModel $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        /** @var ExpressSettingsModel $expressSettings */
        $expressSettings = $this->settingsService->getSettings(null, SettingsTable::EXPRESS_CHECKOUT);
        if (!$expressSettings || !$expressSettings->getLoginActive()) {
            return;
        }

        $view = $args->getSubject()->View();
        $requestParams = $args->getRequest()->getParams();

        if ($requestParams['sTarget'] === 'checkout' && $requestParams['sTargetAction'] === 'confirm') {
            $view->assign('paypalUnifiedEcLoginActive', true);
            $view->assign('paypalUnifiedModeSandbox', $generalSettings->getSandbox());
            $view->assign('paypalUnifiedUseInContext', $generalSettings->getUseInContext());
            $view->assign('paypalUnifiedEcButtonStyleColor', $expressSettings->getButtonStyleColor());
            $view->assign('paypalUnifiedEcButtonStyleShape', $expressSettings->getButtonStyleShape());
            $view->assign('paypalUnifiedEcButtonStyleSize', $expressSettings->getButtonStyleSize());
        }
    }

    /**
     * before the express checkout payment can be executed, the address and amount, which contains the shipping costs,
     * must be updated, because they may have changed during the process
     *
     * @param string $paymentId
     *
     * @throws \Exception
     */
    private function patchAddressAndAmount($paymentId)
    {
        try {
            $orderVariables = $this->session->get('sOrderVariables');
            $userData = $orderVariables['sUserData'];
            $basketData = $orderVariables['sBasket'];

            $shippingAddress = $this->paymentAddressService->getShippingAddress($userData);
            $addressPatch = new PaymentAddressPatch($shippingAddress);

            $requestParams = new PaymentBuilderParameters();
            $requestParams->setWebProfileId('temporary');
            $requestParams->setBasketData($basketData);
            $requestParams->setUserData($userData);

            $paymentStruct = $this->paymentBuilder->getPayment($requestParams);
            $amountPatch = new PaymentAmountPatch($paymentStruct->getTransactions()->getAmount());

            $this->clientService->setPartnerAttributionId(PartnerAttributionId::PAYPAL_EXPRESS_CHECKOUT);
            $this->paymentResource->patch($paymentId, [$addressPatch, $amountPatch]);
        } catch (\Exception $exception) {
            $this->exceptionHandlerService->handle($exception, 'patch the payment for express checkout');
            throw $exception;
        }
    }
}
