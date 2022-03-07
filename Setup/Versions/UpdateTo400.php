<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\ColumnService;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentInstaller;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModelFactory;

class UpdateTo400
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var PaymentModelFactory
     */
    private $paymentModelFactory;

    /**
     * @var ColumnService
     */
    private $columnService;

    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        PaymentMethodProviderInterface $paymentMethodProvider,
        PaymentModelFactory $paymentModelFactory,
        ColumnService $columnService
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->paymentModelFactory = $paymentModelFactory;
        $this->columnService = $columnService;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->moveIntent();
        $this->addButtonStyleToGeneralSettings();
        $this->installNewPaymentMethods();
        $this->addPayUponInvoiceSettingsTable();
        $this->addAdvancedCreditDebitCardSettingsTable();
        $this->insertDefaultButtonStyle();
        $this->addSandboxCredentialsToGeneralSettings();
        $this->addPayerIdToGeneralSettings();
        $this->addPpcpIndicatorToPlusSettings();
        $this->removeMerchantLocationSetting();
        $this->migrateLandingPageType();
        $this->makeShopIdUnique([
            'swag_payment_paypal_unified_settings_general',
            'swag_payment_paypal_unified_settings_installments',
            'swag_payment_paypal_unified_settings_express',
            'swag_payment_paypal_unified_settings_plus',
        ]);
    }

    /**
     * @return void
     */
    private function moveIntent()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'intent')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `intent` varchar(255) default "CAPTURE";'
            );
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_express', 'intent')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_express`
                DROP COLUMN `intent`;'
            );
        }
    }

    /**
     * @return void
     */
    private function addButtonStyleToGeneralSettings()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'button_style_color')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `button_style_color` varchar(255) NULL;'
            );
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'button_style_shape')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `button_style_shape` varchar(255) NULL;'
            );
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'button_style_size')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `button_style_size` varchar(255) NULL;'
            );
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'button_locale')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `button_locale` varchar(255) NULL;'
            );
        }
    }

    /**
     * @return void
     */
    private function installNewPaymentMethods()
    {
        (new PaymentInstaller($this->paymentMethodProvider, $this->paymentModelFactory, $this->modelManager))->installPayments();
    }

    /**
     * @return void
     */
    private function addPayUponInvoiceSettingsTable()
    {
        if (!$this->connection->getSchemaManager()->tablesExist(['swag_payment_paypal_unified_settings_pay_upon_invoice'])) {
            $this->connection->executeQuery(
                <<<'SQL'
CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_pay_upon_invoice
(
    `id`                            INT(11)    UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                       INT(11)    NOT NULL,
    `onboarding_completed`          TINYINT(1) NOT NULL,
    `sandbox_onboarding_completed`  TINYINT(1) NOT NULL,
    `active`                        TINYINT(1) NOT NULL,
    `customer_service_instructions` TEXT       NULL,
    CONSTRAINT unique_shop_id UNIQUE (`shop_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
SQL
            );
        }
    }

    /**
     * @return void
     */
    private function addAdvancedCreditDebitCardSettingsTable()
    {
        if (!$this->connection->getSchemaManager()->tablesExist(['swag_payment_paypal_unified_settings_advanced_credit_debit_card'])) {
            $this->connection->executeQuery(
                <<<'SQL'
CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_settings_advanced_credit_debit_card (
    `id`                           INT(11)    UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id`                      INT(11)    NOT NULL,
    `onboarding_completed`         TINYINT(1) NOT NULL,
    `sandbox_onboarding_completed` TINYINT(1) NOT NULL,
    `active`                       TINYINT(1) NOT NULL,
    CONSTRAINT unique_shop_id UNIQUE (`shop_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;
SQL
            );
        }
    }

    /**
     * @return void
     */
    private function insertDefaultButtonStyle()
    {
        $this->connection->executeQuery(
            "UPDATE swag_payment_paypal_unified_settings_general
             SET `button_style_color` = 'gold',
                 `button_style_shape` = 'rect',
                 `button_style_size` = 'large',
                 `button_locale` = '';"
        );
    }

    /**
     * @return void
     */
    private function addSandboxCredentialsToGeneralSettings()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'sandbox_client_id')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `sandbox_client_id` varchar(255) NULL;'
            );
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'sandbox_client_secret')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `sandbox_client_secret` varchar(255) NULL;'
            );
        }
    }

    /**
     * @return void
     */
    private function addPayerIdToGeneralSettings()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'paypal_payer_id')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `paypal_payer_id` varchar(255) NULL;'
            );
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'sandbox_paypal_payer_id')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                ADD `sandbox_paypal_payer_id` varchar(255) NULL;'
            );
        }
    }

    /**
     * @return void
     */
    private function addPpcpIndicatorToPlusSettings()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_plus', 'ppcp_active')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_plus`
                ADD `ppcp_active` TINYINT(1) NULL;'
            );
        }

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_plus', 'sandbox_ppcp_active')) {
            $this->connection->executeQuery(
                'ALTER TABLE `swag_payment_paypal_unified_settings_plus`
                ADD `sandbox_ppcp_active` TINYINT(1) NULL;'
            );
        }
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    private function removeMerchantLocationSetting()
    {
        $sql = <<<'SQL'
ALTER TABLE `swag_payment_paypal_unified_settings_general`
DROP COLUMN `merchant_location`;
SQL;

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'merchant_location')) {
            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    private function migrateLandingPageType()
    {
        $this->connection->executeQuery('UPDATE swag_payment_paypal_unified_settings_general SET landing_page_type = UPPER(landing_page_type)');
    }

    /**
     * @param array<int, string> $tables
     *
     * @throws Exception
     *
     * @return void
     */
    private function makeShopIdUnique(array $tables)
    {
        foreach ($tables as $tableName) {
            if ($this->hasShopIdColumnUniqueIndex($tableName)) {
                continue;
            }

            $sql = "ALTER TABLE $tableName ADD CONSTRAINT unique_shop_id UNIQUE (`shop_id`)";
            $this->connection->executeQuery($sql);
        }
    }

    /**
     * @param string $tableName
     *
     * @throws Exception
     *
     * @return bool
     */
    private function hasShopIdColumnUniqueIndex($tableName)
    {
        $sql = "SHOW index FROM $tableName WHERE Column_name LIKE 'shop_id' AND Key_name LIKE 'unique_shop_id';";

        $result = $this->connection->fetchAll($sql);

        if (empty($result)) {
            return false;
        }

        return true;
    }
}
