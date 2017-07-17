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

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\HttpClient\RequestException;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\OrderCreditInfoService;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Credit;

class Installments implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ValidationService
     */
    private $validationService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param SettingsServiceInterface $settingsService
     * @param ValidationService        $validationService
     * @param Connection               $connection
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        ValidationService $validationService,
        Connection $connection
    ) {
        $this->settingsService = $settingsService;
        $this->validationService = $validationService;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchDetail',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [['onPostDispatchCheckout'], ['onConfirmInstallments']],
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchDetail(ActionEventArgs $args)
    {
        $settings = $this->settingsService->getSettings();
        if (!$settings || !$settings->getActive() || !$settings->getInstallmentsActive()) {
            return;
        }

        $installmentsDisplayKind = $settings->getInstallmentsPresentmentDetail();

        if ($installmentsDisplayKind === 0) {
            return;
        }

        $view = $args->getSubject()->View();

        $productPrice = $view->getAssign('sArticle')['price_numeric'];

        if (!$this->validationService->validatePrice($productPrice)) {
            $view->assign('paypalInstallmentsNotAvailable', true);

            return;
        }

        $view->assign('paypalInstallmentsMode', $installmentsDisplayKind === 1 ? 'simple' : 'cheapest');
        $view->assign('paypalInstallmentsProductPrice', $productPrice);
        $view->assign('paypalInstallmentsPageType', 'detail');
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchCheckout(ActionEventArgs $args)
    {
        $action = $args->getRequest()->getActionName();

        if ($action !== 'cart' && $action !== 'confirm') {
            return;
        }

        $settings = $this->settingsService->getSettings();
        if (!$settings || !$settings->getActive() || !$settings->getInstallmentsActive()) {
            return;
        }

        $view = $args->getSubject()->View();
        $selectedPaymentMethodId = (int) $view->getAssign('sPayment')['id'];
        $paymentMethodProvider = new PaymentMethodProvider();
        $installmentsPaymentId = $paymentMethodProvider->getPaymentId($this->connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $installmentsDisplayKind = $settings->getInstallmentsPresentmentCart();

        //If the selected payment method is Installments, we can not return here, because in any case, the complete financing list should be displayed.
        if ($installmentsDisplayKind === 0 && $selectedPaymentMethodId !== $installmentsPaymentId) {
            return;
        }

        $productPrice = $view->getAssign('sBasket')['AmountNumeric'];

        if (!$this->validationService->validatePrice($productPrice)) {
            return;
        }

        $view->assign('paypalInstallmentsMode', $installmentsDisplayKind === 1 ? 'simple' : 'cheapest');
        $view->assign('paypalInstallmentsProductPrice', $productPrice);
        $view->assign('paypalInstallmentsPageType', 'cart');

        /*
        * If paypal installments is currently selected, we can request all financing information from the api.
        * A complete new template will then be loaded.
        */
        if ($action === 'confirm' && $selectedPaymentMethodId === $installmentsPaymentId) {
            $view->assign('paypalInstallmentsRequestCompleteList', true);
        }
    }

    /**
     * Fetches data for the installments finishing process.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onConfirmInstallments(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        $basketId = $request->get('basketId');
        $installmentsFlag = $request->get('installments');

        if (!$installmentsFlag || $paymentId === null || $payerId === null || $request->getActionName() !== 'confirm') {
            return;
        }

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        /** @var PaymentResource $paymentResource */
        $paymentResource = $args->getSubject()->get('paypal_unified.payment_resource');

        try {
            $payment = $paymentResource->get($paymentId);
            $view->assign('paypalInstallmentsCredit', $payment['credit_financing_offered']);
            $view->assign('paypalInstallmentsPaymentId', $paymentId);
            $view->assign('paypalInstallmentsPayerId', $payerId);
            $view->assign('paypalInstallmentsBasketId', $basketId);

            $creditStruct = Credit::fromArray($payment['credit_financing_offered']);

            /** @var OrderCreditInfoService $creditInfoService */
            $creditInfoService = $controller->get('paypal_unified.installments.order_credit_info_service');
            $creditInfoService->saveCreditInfo($creditStruct, $payment['id']);

            //Load the custom confirm page
            $view->loadTemplate('frontend/paypal_unified/installments/return/confirm.tpl');
        } catch (RequestException $requestException) {
            $controller->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'shippingPayment',
                'paypal_unified_error_code' => '5', //Installments error
            ]);
        }
    }
}
