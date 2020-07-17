<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Connection;

class FirstRunWizardInstaller
{
    public function saveConfiguration(Connection $connection, array $configuration)
    {
        $connection->insert('swag_payment_paypal_unified_settings_general', [
            'shop_id' => 1,
            'active' => 1,
            'client_id' => $configuration['clientId'],
            'client_secret' => $configuration['clientSecret'],
            'sandbox' => $configuration['sandbox'],
            'show_sidebar_logo' => 0,
            'send_order_number' => 0,
            'use_in_context' => 0,
            'log_level' => 1,
            'display_errors' => 0,
            'advertise_returns' => 0,
            'advertise_installments' => 1,
            'use_smart_payment_buttons' => 0,
            'merchant_location' => 'germany',
            'landing_page_type' => 'Login',
        ]);

        if ($configuration['payPalPlusEnabled']) {
            $connection->insert('swag_payment_paypal_unified_settings_plus', [
                'shop_id' => 1,
                'active' => 1,
                'restyle' => 0,
                'integrate_third_party_methods' => 0,
            ]);
        }
    }
}
