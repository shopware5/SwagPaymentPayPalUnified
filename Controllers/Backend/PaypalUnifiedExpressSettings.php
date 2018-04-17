<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class Shopware_Controllers_Backend_PaypalUnifiedExpressSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = ExpressSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'express';

    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('paypal_unified.settings_service');

        /** @var ExpressSettingsModel $settings */
        $settings = $settingsService->getSettings($shopId, SettingsTable::EXPRESS_CHECKOUT);

        if ($settings !== null) {
            $this->view->assign('express', $settings->toArray());
        }
    }
}
