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
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class Installments implements SubscriberInterface
{
    /**
     * @var Settings
     */
    private $settings;

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
        $this->settings = $settingsService->getSettings();
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
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [['onPostDispatchCheckout'], ['confirmInstallments']],
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchDetail(ActionEventArgs $args)
    {
        if (!$this->settings || !$this->settings->getActive() || !$this->settings->getInstallmentsActive()) {
            return;
        }

        $installmentsDisplayKind = $this->settings->getInstallmentsPresentmentDetail();

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

        if (!$this->settings || !$this->settings->getActive() || !$this->settings->getInstallmentsActive()) {
            return;
        }

        $view = $args->getSubject()->View();
        $selectedPaymentMethodId = (int) $view->getAssign('sPayment')['id'];
        $paymentMethodProvider = new PaymentMethodProvider();
        $installmentsPaymentId = $paymentMethodProvider->getPaymentId($this->connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $installmentsDisplayKind = $this->settings->getInstallmentsPresentmentCart();

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

        if ($action === 'confirm') {
            /*
             * If paypal installments is currently selected, we can request all financing information from the api.
             * A complete new template will then be loaded.
             */
            if ($selectedPaymentMethodId === $installmentsPaymentId) {
                $view->assign('paypalInstallmentsRequestCompleteList', true);
            }
        }
    }

    /**
     * Fetches data for the installments finishing process.
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function confirmInstallments(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $controller->Request();

        if ($request->getActionName() !== 'confirm' || (int) $request->getParam('executePayment') !== 1) {
            return;
        }

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        $view->assign('paypalInstallmentsMode', 'selected');
        $view->assign('paypalSelectedInstallment', [
            'foo' => 'bar',
        ]);
    }
}
