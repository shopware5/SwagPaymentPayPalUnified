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

use SwagPaymentPayPalUnified\Models\Settings\Plus as PlusSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class Shopware_Controllers_Backend_PaypalUnifiedPlusSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = PlusSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'plus';

    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('paypal_unified.settings_service');

        /** @var PlusSettingsModel $settings */
        $settings = $settingsService->getSettings($shopId, SettingsTable::PLUS);

        if ($settings !== null) {
            $this->view->assign('plus', $settings->toArray());
        }
    }
}
