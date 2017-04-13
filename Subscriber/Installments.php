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
use SwagPaymentPayPalUnified\PayPalBundle\Resources\InstallmentsResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Installments\FinancingResponse;

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
     * @var InstallmentsResource
     */
    private $installmentsResource;

    /**
     * @param SettingsServiceInterface $settingsService
     * @param ValidationService        $validationService
     * @param InstallmentsResource     $installmentsResource
     */
    public function __construct(
        SettingsServiceInterface $settingsService,
        ValidationService $validationService,
        InstallmentsResource $installmentsResource
    ) {
        $this->settings = $settingsService->getSettings();
        $this->validationService = $validationService;
        $this->installmentsResource = $installmentsResource;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onPostDispatchDetail',
        ];
    }

    /**
     * @param ActionEventArgs $args
     */
    public function onPostDispatchDetail(ActionEventArgs $args)
    {
        if (!$this->settings) {
            return;
        }

        if (!$this->settings->getActive()) {
            return;
        }

        if (!$this->settings->getInstallmentsActive()) {
            return;
        }

        $installmentsDisplayKind = $this->settings->getInstallmentsPresentmentDetail();

        if ($installmentsDisplayKind === 0) {
            return;
        }

        $view = $args->getSubject()->View();

        $product = $view->getAssign('sArticle');
        $productPrice = $product['price_numeric'];

        if (!$this->validationService->validatePrice($productPrice)) {
            $view->assign('payPalUnifiedInstallmentsNotAvailable', true);

            return;
        }

        switch ($installmentsDisplayKind) {
            case 1: //simple
                $view->assign('payPalUnifiedInstallmentsDisplayKind', 'simple');
                $view->assign('payPalUnifiedInstallmentsProductPrice', $productPrice);

                break;

            case 2: //cheapest rate
//                $financingResponse = FinancingResponse::fromArray($response['financing_options'][0]);
                $view->assign('payPalUnifiedInstallmentsDisplayKind', 'cheapest');

                break;
        }
    }
}
