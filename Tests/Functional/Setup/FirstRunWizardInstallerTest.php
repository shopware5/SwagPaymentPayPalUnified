<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Setup\FirstRunWizardInstaller;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class FirstRunWizardInstallerTest extends TestCase
{
    use DatabaseTestCaseTrait;

    public function testInstallationLandingPageTypeShouldBeSet()
    {
        $installer = $this->getFirstRunWizardInstaller();
        $connection = $this->getConnection();
        $config = $this->getDefaultConfig();
        $installer->saveConfiguration($connection, $config);

        $lastInsertId = $connection->lastInsertId();
        $result = $connection->createQueryBuilder()
            ->select([
                $config['sandbox'] ? 'sandbox_client_id' : 'client_id',
                $config['sandbox'] ? 'sandbox_client_secret' : 'client_secret',
                'landing_page_type',
            ])
            ->from('swag_payment_paypal_unified_settings_general')
            ->where('id = :lastInsertId')
            ->setParameter('lastInsertId', $lastInsertId)
            ->execute()
            ->fetch();

        static::assertSame($config['clientId'], $config['sandbox'] ? $result['sandbox_client_id'] : $result['client_id']);
        static::assertSame($config['clientSecret'], $config['sandbox'] ? $result['sandbox_client_secret'] : $result['client_secret']);
        static::assertSame('Login', $result['landing_page_type']);
    }

    /**
     * @return array
     */
    private function getDefaultConfig()
    {
        return [
            'clientId' => 'testClientId',
            'clientSecret' => 'testClientSecret',
            'sandbox' => true,
            'payPalPlusEnabled' => false,
        ];
    }

    /**
     * @return FirstRunWizardInstaller
     */
    private function getFirstRunWizardInstaller()
    {
        return new FirstRunWizardInstaller();
    }

    /**
     * @return Connection
     */
    private function getConnection()
    {
        return Shopware()->Container()->get('dbal_connection');
    }
}
