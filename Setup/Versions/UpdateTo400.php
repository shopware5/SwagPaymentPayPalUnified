<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
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
     * @var PaymentMethodProvider
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
        PaymentMethodProvider $paymentMethodProvider,
        PaymentModelFactory $paymentModelFactory,
        ColumnService $columnService
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->paymentModelFactory = $paymentModelFactory;
        $this->columnService = $columnService;
    }

    public function update()
    {
        $this->moveIntent();
        $this->addButtonStyleToGeneralSettings();
        $this->installNewPaymentMethods();
        $this->insertDefaultButtonStyle();
    }

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

    private function installNewPaymentMethods()
    {
        (new PaymentInstaller($this->paymentMethodProvider, $this->paymentModelFactory, $this->modelManager))->installPayments();
    }

    private function insertDefaultButtonStyle()
    {
        $this->connection->executeQuery(
            'UPDATE swag_payment_paypal_unified_settings_general
                    SET `button_style_color` = "gold",
                        `button_style_shape` = "rect",
                        `button_style_size` = "large",
                        `button_locale` = ""
                    ;'
        );
    }
}
