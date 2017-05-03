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
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
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
     * @param SettingsServiceInterface $settingsService
     * @param ValidationService        $validationService
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        ValidationService $validationService
    ) {
        $this->settings = $settingsService->getSettings();
        $this->validationService = $validationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchDetail',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
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

        $installmentsDisplayKind === 1 ? $view->assign('paypalInstallmentsMode', 'simple') : $view->assign('paypalInstallmentsMode', 'cheapest');
        $view->assign('paypalProductPrice', $productPrice);
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

        $installmentsDisplayKind = $this->settings->getInstallmentsPresentmentCart();

        if ($installmentsDisplayKind === 0) {
            return;
        }

        $view = $args->getSubject()->View();
        $productPrice = $view->getAssign('sBasket')['AmountNumeric'];

        if (!$this->validationService->validatePrice($productPrice)) {
            return;
        }

        $installmentsDisplayKind === 1 ? $view->assign('paypalInstallmentsMode', 'simple') : $view->assign('paypalInstallmentsMode', 'cheapest');
        $view->assign('paypalProductPrice', $productPrice);
        $view->assign('paypalInstallmentsPageType', 'cart');
    }
}
