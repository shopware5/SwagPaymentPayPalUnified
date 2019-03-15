<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
