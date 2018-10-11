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
     * @param CrudService  $attributeCrudService
     * @param ModelManager $modelManager
     * @param Connection   $connection
     */
    public function __construct(CrudService $attributeCrudService, ModelManager $modelManager, Connection $connection)
    {
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
    }

    /**
     * @param string $oldVersion
     */
    public function update($oldVersion)
    {
        if (version_compare($oldVersion, '1.0.2', '<=')) {
            $this->updateTo103();
        }

        if (version_compare($oldVersion, '1.0.7', '<=')) {
            $this->updateTo110();
        }

        if (version_compare($oldVersion, '1.1.0', '<=')) {
            $this->updateTo111();
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
        $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_general` 
                ADD COLUMN `landing_page_type` VARCHAR(255);
                UPDATE `swag_payment_paypal_unified_settings_general` SET `landing_page_type` = "Login";';

        $this->connection->executeQuery($sql);
    }

    private function updateTo111()
    {
        $sql = 'ALTER TABLE `swag_payment_paypal_unified_settings_general` 
                DROP COLUMN `logo_image`;';

        $this->connection->executeQuery($sql);
    }
}
