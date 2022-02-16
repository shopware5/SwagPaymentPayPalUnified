<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

/**
 * @extends \Shopware_Controllers_Backend_Application<PayUponInvoice>
 */
class Shopware_Controllers_Backend_PaypalUnifiedPayUponInvoiceSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = PayUponInvoice::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'payUponInvoice';

    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('paypal_unified.settings_service');

        $settings = $settingsService->getSettings($shopId, SettingsTable::PAY_UPON_INVOICE);

        if ($settings instanceof PayUponInvoice) {
            $this->view->assign($this->alias, $settings->toArray());
        }
    }
}
