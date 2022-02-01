<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModelFactory;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo400;

class Updater
{
    /**
     * @var CrudService
     */
    private $attributeCrudService;

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
        CrudService $attributeCrudService,
        ModelManager $modelManager,
        Connection $connection,
        PaymentMethodProvider $paymentMethodProvider,
        PaymentModelFactory $paymentModelFactory
    ) {
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->paymentModelFactory = $paymentModelFactory;

        $this->columnService = new ColumnService($this->connection);
    }

    /**
     * @param string $oldVersion
     */
    public function update($oldVersion)
    {
        if (\version_compare($oldVersion, '1.0.2', '<=')) {
            $this->updateTo103();
        }

        if (\version_compare($oldVersion, '1.0.7', '<=')) {
            $this->updateTo110();
        }

        if (\version_compare($oldVersion, '1.1.0', '<=')) {
            $this->updateTo111();
        }

        if (\version_compare($oldVersion, '1.1.1', '<=')) {
            $this->updateTo112();
        }

        if (\version_compare($oldVersion, '2.0.3', '<=')) {
            $this->updateTo210();
        }

        if (\version_compare($oldVersion, '2.1.3', '<=')) {
            $this->updateTo220();
        }

        if (\version_compare($oldVersion, '2.3.0', '<=')) {
            $this->updateTo240();
        }

        if (\version_compare($oldVersion, '2.4.1', '<=')) {
            $this->updateTo250();
        }

        if (\version_compare($oldVersion, '2.6.0', '<=')) {
            $this->updateTo261();
        }

        if (\version_compare($oldVersion, '2.7.0', '<=')) {
            $this->updateTo270();
        }

        if (\version_compare($oldVersion, '3.0.0', '<=')) {
            $this->updateTo300();
        }

        if (\version_compare($oldVersion, '3.0.3', '<=')) {
            $this->updateTo303();
        }

        if (\version_compare($oldVersion, '3.0.4', '<=')) {
            $this->updateTo304();
        }

        if (\version_compare($oldVersion, '4.0.0', '<=')) {
            (new UpdateTo400(
                $this->modelManager,
                $this->connection,
                $this->paymentMethodProvider,
                $this->paymentModelFactory,
                $this->columnService
            ))->update();
        }
    }

    private function updateTo103()
    {
        $this->attributeCrudService->update(
            's_core_paymentmeans_attributes',
            'swag_paypal_unified_plus_iframe_payment_logo',
            'string',
            [
                'position' => -99,
                'displayInBackend' => true,
                'label' => 'Payment logo for iFrame',
                'helpText' => 'Simply put an URL to an image here, if you want to show a logo for this payment in the PayPal Plus iFrame.<br><ul><li>The URL must be secure (https)</li><li>The image size must be maximum 100x25px</li></ul>',
            ]
        );

        $this->modelManager->generateAttributeModels(['s_core_paymentmeans_attributes']);
    }

    private function updateTo110()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'landing_page_type')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                    ADD COLUMN `landing_page_type` VARCHAR(255);
                    UPDATE `swag_payment_paypal_unified_settings_general`
                    SET `landing_page_type` = "Login";';

            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo111()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'logo_image')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                    DROP COLUMN `logo_image`;';

            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo112()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_express', 'off_canvas_active')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_express`
                ADD `off_canvas_active` TINYINT(1) NOT NULL;
                UPDATE `swag_payment_paypal_unified_settings_express`
                SET `off_canvas_active` = 1;';

            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo210()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_express', 'listing_active')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_express`
                ADD `listing_active` TINYINT(1) NOT NULL;
                UPDATE `swag_payment_paypal_unified_settings_express`
                SET `listing_active` = 0;';

            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo220()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_express', 'button_locale')) {
            $sql = "ALTER TABLE `swag_payment_paypal_unified_settings_express`
                ADD `button_locale` VARCHAR(5) NOT NULL;
                UPDATE `swag_payment_paypal_unified_settings_express`
                SET `button_locale` = '';";

            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo240()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'use_smart_payment_buttons')) {
            $query = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_general`
ADD `use_smart_payment_buttons` TINYINT(1) NOT NULL,
ADD `merchant_location` VARCHAR(255) NOT NULL;
UPDATE `swag_payment_paypal_unified_settings_general`
SET `use_smart_payment_buttons` = 0, `merchant_location` = :location;
SQL;

            $this->connection->executeQuery($query, ['location' => GeneralSettingsModel::MERCHANT_LOCATION_GERMANY]);
        }
    }

    private function updateTo250()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'submit_cart')) {
            $query = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_general`
ADD `submit_cart` TINYINT(1) NOT NULL;
UPDATE `swag_payment_paypal_unified_settings_general`
SET `submit_cart` = 1;
SQL;

            $this->connection->executeQuery($query);
        }
    }

    private function updateTo261()
    {
        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'advertise_installments')) {
            $query = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_general`
ADD `advertise_installments` TINYINT(1) NOT NULL;
UPDATE `swag_payment_paypal_unified_settings_general`
SET `advertise_installments` = 1;
SQL;

            $this->connection->executeQuery($query);
        }
    }

    private function updateTo270()
    {
        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'advertise_returns')) {
            $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_general`
                    DROP COLUMN `advertise_returns`;';

            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo300()
    {
        $sql = <<<SQL
UPDATE `s_core_paymentmeans`
SET `active` = '0'
WHERE `s_core_paymentmeans`.`name` = 'SwagPaymentPayPalUnifiedInstallments';
SQL;
        $this->connection->executeQuery($sql);

        if (!$this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'advertise_installments')) {
            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_installments`
ADD `advertise_installments` TINYINT(1) NOT NULL;
SQL;
            $this->connection->executeQuery($sql);
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_general', 'advertise_installments')) {
            $sql = <<<SQL
UPDATE `swag_payment_paypal_unified_settings_installments` AS `installments`, `swag_payment_paypal_unified_settings_general` AS `general`
SET `installments`.`advertise_installments` = `general`.`advertise_installments`
WHERE `installments`.`shop_id` = `general`.`shop_id`;
SQL;
            $this->connection->executeQuery($sql);

            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_general`
DROP COLUMN `advertise_installments`;
SQL;
            $this->connection->executeQuery($sql);
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'active')) {
            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_installments`
DROP COLUMN `active`;
SQL;
            $this->connection->executeQuery($sql);
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'presentment_detail')) {
            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_installments`
DROP COLUMN `presentment_detail`;
SQL;
            $this->connection->executeQuery($sql);
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'presentment_cart')) {
            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_installments`
DROP COLUMN `presentment_cart`;
SQL;
            $this->connection->executeQuery($sql);
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'show_logo')) {
            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_installments`
DROP COLUMN `show_logo`;
SQL;
            $this->connection->executeQuery($sql);
        }

        if ($this->columnService->checkIfColumnExist('swag_payment_paypal_unified_settings_installments', 'intent')) {
            $sql = <<<SQL
ALTER TABLE `swag_payment_paypal_unified_settings_installments`
DROP COLUMN `intent`;
SQL;
            $this->connection->executeQuery($sql);
        }
    }

    private function updateTo303()
    {
        $orderAttributeIds = $this->connection->createQueryBuilder()
            ->select(['sOrderAttributes.id'])
            ->from('s_order_attributes', 'sOrderAttributes')
            ->join('sOrderAttributes', 's_order', 'sOrder', 'sOrder.id = sOrderAttributes.orderID')
            ->where('sOrder.paymentID != :paymentId')
            ->andWhere('sOrderAttributes.swag_paypal_unified_payment_type LIKE :paymentType')
            ->andWhere('sOrder.ordertime > :orderTime')
            ->setParameter('paymentId', $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME))
            ->setParameter('paymentType', PaymentType::PAYPAL_CLASSIC)
            ->setParameter('orderTime', '2021-01-13 00:00:00') // Day of 3.0.2 release, which broke the applying of the payment type attribute
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $this->connection->createQueryBuilder()
            ->update('s_order_attributes', 'sOrderAttributes')
            ->set('sOrderAttributes.swag_paypal_unified_payment_type', 'NULL')
            ->where('sOrderAttributes.id IN (:attributeIds)')
            ->setParameter('attributeIds', $orderAttributeIds, Connection::PARAM_INT_ARRAY)
            ->execute();
    }

    private function updateTo304()
    {
        $orderAttributeIds = $this->connection->createQueryBuilder()
            ->select(['sOrderAttributes.id'])
            ->from('s_order_attributes', 'sOrderAttributes')
            ->join('sOrderAttributes', 's_order', 'sOrder', 'sOrder.id = sOrderAttributes.orderID')
            ->where('sOrder.paymentID = :paymentId')
            ->andWhere('sOrderAttributes.swag_paypal_unified_payment_type LIKE :paymentType')
            ->andWhere('sOrder.internalcomment LIKE :description')
            ->andWhere('sOrder.ordertime > :orderTime')
            ->setParameter('paymentId', $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME))
            ->setParameter('paymentType', PaymentType::PAYPAL_PLUS)
            ->setParameter('description', \sprintf('%%%s%%', PaymentInstructionService::INVOICE_INSTRUCTION_DESCRIPTION))
            ->setParameter('orderTime', '2021-01-15 00:00:00') // Day of 3.0.3 release, which broke the applying of the payment type attribute for invoice
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $this->connection->createQueryBuilder()
            ->update('s_order_attributes', 'sOrderAttributes')
            ->set('sOrderAttributes.swag_paypal_unified_payment_type', \sprintf('"%s"', PaymentType::PAYPAL_INVOICE))
            ->where('sOrderAttributes.id IN (:attributeIds)')
            ->setParameter('attributeIds', $orderAttributeIds, Connection::PARAM_INT_ARRAY)
            ->execute();
    }
}
