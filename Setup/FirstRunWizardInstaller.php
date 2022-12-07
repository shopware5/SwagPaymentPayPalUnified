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
    /**
     * @param array<string, mixed> $configuration
     *
     * @return void
     */
    public function saveConfiguration(Connection $connection, array $configuration)
    {
        $connection->insert('swag_payment_paypal_unified_settings_general', [
            'shop_id' => 1,
            'active' => 1,
            'client_id' => $configuration['sandbox'] ? '' : $configuration['clientId'],
            'client_secret' => $configuration['sandbox'] ? '' : $configuration['clientSecret'],
            'sandbox_client_id' => $configuration['sandbox'] ? $configuration['clientId'] : '',
            'sandbox_client_secret' => $configuration['sandbox'] ? $configuration['clientSecret'] : '',
            'sandbox' => $configuration['sandbox'],
            'show_sidebar_logo' => 0,
            'display_errors' => 0,
            'advertise_returns' => 0,
            'use_smart_payment_buttons' => 0,
            'landing_page_type' => 'Login',
        ]);
    }
}
