<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class Shopware_Controllers_Backend_PaypalUnifiedInstallmentsSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = InstallmentsSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'installments';

    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('paypal_unified.settings_service');

        /** @var InstallmentsSettingsModel $settings */
        $settings = $settingsService->getSettings($shopId, SettingsTable::INSTALLMENTS);

        if ($settings !== null) {
            $this->view->assign('installments', $settings->toArray());
        }
    }
}
