<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Plugin\Plugin;

class Installer
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /** @var Connection $connection */
    private $connection;

    /** @var CrudService $attributeCrudService */
    private $attributeCrudService;

    /** @var string $bootstrapPath */
    private $bootstrapPath;

    /**
     * Installer constructor.
     *
     * @param ModelManager $modelManager
     * @param Connection $connection
     * @param CrudService $attributeCrudService
     * @param string $bootstrapPath
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        CrudService $attributeCrudService,
        $bootstrapPath
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->attributeCrudService = $attributeCrudService;
        $this->bootstrapPath = $bootstrapPath;
    }

    /**
     * @param InstallContext $installContext
     * @return bool
     * @throws InstallationException
     */
    public function install(InstallContext $installContext)
    {
        if ($this->hasPayPalClassicInstalled()) {
            throw new InstallationException('This plugin can not be used while PayPal Classic or PayPal Plus are installed and active.');
        }

        $this->createDatabaseTables();
        $this->createPaymentMethod();
        $this->createAttributes();
        $this->createDocumentTemplates();

        return true;
    }

    /**
     * @return bool
     */
    private function hasPayPalClassicInstalled()
    {
        $classicPlugin = $this->modelManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPaypal',
            'active' => 1
        ]);
        $classicPlusPlugin = $this->modelManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPaypalPlus',
            'active' => 1
        ]);

        return $classicPlugin != null || $classicPlusPlugin != null;
    }

    private function createDatabaseTables()
    {
        $sql = "CREATE TABLE IF NOT EXISTS swag_payment_paypal_unified_payment_instruction (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `order_number` VARCHAR(255),
                `bank_name` VARCHAR(255),
                `account_holder` VARCHAR(255),
                `iban` VARCHAR(255),
                `bic` VARCHAR(255),
                `amount` VARCHAR(255),
                `reference` VARCHAR(255),
                `due_date` DATETIME) 
                 ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

        $this->connection->query($sql);
    }

    private function createAttributes()
    {
        $this->attributeCrudService->update('s_order_attributes', 'paypal_payment_type', 'integer');
        $this->modelManager->generateAttributeModels(['s_order_attributes']);
    }

    private function createDocumentTemplates()
    {
        $this->removeDocumentTemplates();

        $sql = "
			INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
			(1, 'PayPal_Unified_Instructions_Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', :footerValue),
			(1, 'PayPal_Unified_Instructions_Content', :contentStyle, :contentValue);
		";

        //Load the assets
        $instructionsContent = file_get_contents($this->bootstrapPath . '/Setup/Assets/Document/PayPal_Unified_Instructions_Content.html');
        $instructionsContentStyle = file_get_contents($this->bootstrapPath . '/Setup/Assets/Document/PayPal_Unified_Instructions_Content_Style.css');
        $instructionsFooter = file_get_contents($this->bootstrapPath . '/Setup/Assets/Document/PayPal_Unified_Instructions_Footer.html');

        $this->connection->executeQuery($sql, [
            'footerValue' => $instructionsFooter,
            'contentStyle' => $instructionsContentStyle,
            'contentValue' => $instructionsContent
        ]);
    }

    private function createPaymentMethod()
    {
        $existingPayment = $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => 'SwagPaymentPayPalUnified'
        ]);

        if ($existingPayment !== null) {
            //If the payment does already exist, we don't need to add it again.
            return;
        }

        $entity = new Payment();
        $entity->setActive(false);
        $entity->setName('SwagPaymentPayPalUnified');
        $entity->setDescription('PayPal');
        $entity->setAdditionalDescription($this->getPaymentLogo() . 'Bezahlung per PayPal - einfach, schnell und sicher.');
        $entity->setAction('PaypalUnified');

        $this->modelManager->persist($entity);
        $this->modelManager->flush($entity);
    }

    private function removeDocumentTemplates()
    {
        $sql = "DELETE FROM s_core_documents_box WHERE `name` LIKE 'PayPal_Unified%'";
        $this->connection->exec($sql);
    }

    private function getPaymentLogo()
    {
        return '<!-- PayPal Logo -->'
        . '<a onclick="window.open(this.href, \'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=500\'); return false;"'
        . ' href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank">'
        . '<img src="{link file=\'frontend/_public/src/img/sidebar-paypal-generic.png\' fullPath}" alt="Logo \'PayPal empfohlen\'">'
        . '</a><br>' . '<!-- PayPal Logo -->';
    }
}
