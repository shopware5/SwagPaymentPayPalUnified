<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use SwagPaymentPayPalUnified\Setup\ColumnService;

class UpdateTo600
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var CrudServiceInterface
     */
    private $crudService;

    /**
     * @var ColumnService
     */
    private $columnService;

    public function __construct(Connection $connection, CrudServiceInterface $crudService, ColumnService $columnService)
    {
        $this->connection = $connection;
        $this->crudService = $crudService;
        $this->columnService = $columnService;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->createAttributes();
        $this->migrateLanguageSettings();
        $this->addShowPayLaterButtonSettings();
        $this->dropInContextSetting();
        $this->disableOxxoPaymentMethod();
    }

    /**
     * @return void
     */
    private function createAttributes()
    {
        $this->crudService->update('s_order_attributes', 'swag_paypal_unified_carrier_was_sent', 'boolean');

        $this->crudService->update('s_order_attributes', 'swag_paypal_unified_carrier', 'string', [
            'displayInBackend' => true,
            'label' => 'Carrier code',
            'helpText' => 'Enter a PayPal carrier code (e.g. DHL_GLOBAL_ECOMMERCE)...',
            'translatable' => true,
            'supportText' => 'PayPal offers tracking for orders processed through PayPal. To use this, specify a default shipping carrier, which can be overwritten in the orders. Find a list of all shipping providers <a target="_blank" href="https://developer.paypal.com/docs/tracking/reference/carriers/">here</a>',
            'position' => 100,
        ]);

        $this->crudService->update('s_premium_dispatch_attributes', 'swag_paypal_unified_carrier', 'string', [
            'displayInBackend' => true,
            'label' => 'Carrier code',
            'helpText' => 'Enter a PayPal carrier code (e.g. DHL_GLOBAL_ECOMMERCE)...',
            'translatable' => true,
            'supportText' => 'PayPal offers tracking for orders processed through PayPal. To use this, specify a default shipping carrier, which can be overwritten in the orders. Find a list of all shipping providers <a target="_blank" href="https://developer.paypal.com/docs/tracking/reference/carriers/">here</a>',
            'position' => 100,
        ]);
    }

    /**
     * @return void
     */
    private function migrateLanguageSettings()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_express', 'button_locale')) {
            $buttonLocals = $this->connection->query('SELECT shop_id, button_locale from swag_payment_paypal_unified_settings_general')->fetchAll(PDO::FETCH_KEY_PAIR);
            $buttonExpressLocals = $this->connection->query('SELECT shop_id, button_locale from swag_payment_paypal_unified_settings_express')->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach ($buttonLocals as $shopId => $buttonLocal) {
                if (empty($buttonLocal) && !empty($buttonExpressLocals[$shopId])) {
                    $this->connection->prepare('UPDATE swag_payment_paypal_unified_settings_general SET button_locale = :button WHERE shop_id = :shopId')->execute(['shopId' => $shopId, 'button' => $buttonExpressLocals[$shopId]]);
                }
            }

            $this->connection->exec('ALTER TABLE swag_payment_paypal_unified_settings_express DROP COLUMN button_locale');
        }
    }

    /**
     * @return void
     */
    private function addShowPayLaterButtonSettings()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'show_pay_later_paypal')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_installments`
                ADD COLUMN `show_pay_later_paypal` TINYINT(1)
                NOT NULL
                DEFAULT 1;';

            $this->connection->exec($sql);
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'show_pay_later_express')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_installments`
                ADD COLUMN `show_pay_later_express` TINYINT(1)
                NOT NULL
                DEFAULT 1;';

            $this->connection->exec($sql);
        }
    }

    /**
     * @return void
     */
    private function dropInContextSetting()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'use_in_context')) {
            $sql = '
                ALTER TABLE `swag_payment_paypal_unified_settings_general`
                DROP COLUMN `use_in_context`;
            ';

            $this->connection->exec($sql);
        }
    }

    /**
     * @return void
     */
    private function disableOxxoPaymentMethod()
    {
        $this->connection->createQueryBuilder()
            ->update('s_core_paymentmeans')
            ->set('active', '0')
            ->set('description', ':newOxxoDescription')
            ->where('name = :oxxoPaymentMethodName')
            ->setParameter('newOxxoDescription', 'OXXO. Do not activate again, the payment method is removed from PayPal plugin.')
            ->setParameter('oxxoPaymentMethodName', 'SwagPaymentPayPalUnifiedOXXO')
            ->execute();
    }
}
