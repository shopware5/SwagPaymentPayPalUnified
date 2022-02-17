<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagPaymentPayPalUnified\Models\Settings\AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

/**
 * @extends \Shopware_Controllers_Backend_Application<AdvancedCreditDebitCard>
 */
class Shopware_Controllers_Backend_PaypalUnifiedAdvancedCreditDebitCardSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = AdvancedCreditDebitCard::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'advanced-credit-debit-card';

    /**
     * @return void
     */
    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('paypal_unified.settings_service');

        $settings = $settingsService->getSettings($shopId, SettingsTable::ADVANCED_CREDIT_DEBIT_CARD);

        if (!$settings instanceof AdvancedCreditDebitCard) {
            return;
        }

        $this->view->assign($this->alias, $settings->toArray());
    }
}
