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
use Shopware\Models\Payment\Payment;
use Shopware\Models\Plugin\Plugin;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

class Installer
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
     * @var CrudService
     */
    private $attributeCrudService;

    /**
     * @var string
     */
    private $bootstrapPath;

    /**
     * @var Shopware_Components_Translation
     */
    private $translation;

    /**
     * @param string $bootstrapPath
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        CrudService $attributeCrudService,
        Shopware_Components_Translation $translation,
        $bootstrapPath
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->attributeCrudService = $attributeCrudService;
        $this->translation = $translation;
        $this->bootstrapPath = $bootstrapPath;
    }

    /**
     * @throws InstallationException
     *
     * @return bool
     */
    public function install()
    {
        if ($this->hasPayPalClassicInstalled()) {
            throw new InstallationException('This plugin can not be used while PayPal Classic, PayPal Plus or PayPal Installments are installed and active.');
        }

        $this->createDatabaseTables();
        $this->createUnifiedPaymentMethod();
        $this->createInstallmentsPaymentMethod();
        $this->createAttributes();
        $this->createDocumentTemplates();
        $this->migrate();
        $this->writeTranslation();

        return true;
    }

    /**
     * @return bool
     */
    private function hasPayPalClassicInstalled()
    {
        $classicPlugin = $this->modelManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPaypal',
            'active' => 1,
        ]);
        $classicPlusPlugin = $this->modelManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPaypalPlus',
            'active' => 1,
        ]);
        $classicInstallmentsPlugin = $this->modelManager->getRepository(Plugin::class)->findOneBy([
            'name' => 'SwagPaymentPayPalInstallments',
            'active' => 1,
        ]);

        return $classicPlugin !== null || $classicPlusPlugin !== null || $classicInstallmentsPlugin !== null;
    }

    private function createDatabaseTables()
    {
        $sql = file_get_contents($this->bootstrapPath . '/Setup/Assets/tables.sql');

        $this->connection->query($sql);
    }

    private function createAttributes()
    {
        $this->attributeCrudService->update('s_order_attributes', 'swag_paypal_unified_payment_type', 'string');
        $this->attributeCrudService->update(
            's_core_paymentmeans_attributes',
            'swag_paypal_unified_display_in_plus_iframe',
            'boolean',
            [
                'position' => -100,
                'displayInBackend' => true,
                'label' => 'Display in PayPal Plus iFrame',
                'helpText' => 'Activate this option, to display this payment method in the PayPal Plus iFrame',
            ]
        );
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

        $this->modelManager->generateAttributeModels(['s_order_attributes', 's_core_paymentmeans_attributes']);
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
            'contentValue' => $instructionsContent,
        ]);
    }

    private function createUnifiedPaymentMethod()
    {
        $existingPayment = $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
        ]);

        if ($existingPayment !== null) {
            //If the payment does already exist, we don't need to add it again.
            return;
        }

        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(-100);
        $payment->setName(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $payment->setDescription('PayPal');
        $payment->setAdditionalDescription($this->getUnifiedPaymentLogo() . 'Bezahlung per PayPal - einfach, schnell und sicher.');
        $payment->setAction('PaypalUnified');

        $this->modelManager->persist($payment);
        $this->modelManager->flush($payment);
    }

    private function createInstallmentsPaymentMethod()
    {
        $existingPayment = $this->modelManager->getRepository(Payment::class)->findOneBy([
            'name' => PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME,
        ]);

        if ($existingPayment !== null) {
            //If the payment does already exist, we don't need to add it again.
            return;
        }

        $payment = new Payment();
        $payment->setActive(false);
        $payment->setPosition(-99);
        $payment->setName(PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        $payment->setDescription('Ratenzahlung Powered by PayPal');
        $payment->setAdditionalDescription('Wir ermöglichen Ihnen die Finanzierung Ihres Einkaufs mithilfe der Ratenzahlung Powered by PayPal. In Sekundenschnelle, vollständig online, vorbehaltlich Bonitätsprüfung.');
        $payment->setAction('PaypalUnifiedInstallments');

        $this->modelManager->persist($payment);
        $this->modelManager->flush($payment);
    }

    private function removeDocumentTemplates()
    {
        $sql = "DELETE FROM s_core_documents_box WHERE `name` LIKE 'PayPal_Unified%'";
        $this->connection->exec($sql);
    }

    private function migrate()
    {
        $sql = file_get_contents($this->bootstrapPath . '/Setup/Assets/migration.sql');

        $this->connection->query($sql);
    }

    /**
     * @return string
     */
    private function getUnifiedPaymentLogo()
    {
        return '<!-- PayPal Logo -->'
        . '<a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank" rel="noopener">'
        . '<img src="{link file=\'frontend/_public/src/img/sidebar-paypal-generic.png\' fullPath}" alt="Logo \'PayPal empfohlen\'">'
        . '</a><br><!-- PayPal Logo -->';
    }

    private function writeTranslation()
    {
        /** @var array $translationKeys */
        $translationKeys = $this->getTranslationKeys();

        $this->translation->write(
            2,
            'config_payment',
            $translationKeys['SwagPaymentPayPalUnified'],
            [
                'description' => 'PayPal',
                'additionalDescription' => '<!-- PayPal Logo --><a href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank" rel="noopener">'
                    . '<img src="{link file=\'frontend/_public/src/img/sidebar-paypal-generic.png\' fullPath}" alt="Logo \'PayPal recommended\'">'
                    . '</a><br><!-- PayPal Logo -->Paying with PayPal - easy, fast and secure.',
            ],
            true
        );

        $this->translation->write(
            2,
            'config_payment',
            $translationKeys['SwagPaymentPayPalUnifiedInstallments'],
            [
                'description' => 'Installments powered by PayPal',
                'additionalDescription' => 'We allow you to finance your purchase with installments powered by PayPal. '
                    . 'Only a few seconds, completely online, conditional credit assessment.',
            ],
            true
        );
    }

    /**
     * @return array
     */
    private function getTranslationKeys()
    {
        return $this->modelManager->getDBALQueryBuilder()
            ->select('name, id')
            ->from('s_core_paymentmeans', 'pm')
            ->where("pm.name = 'SwagPaymentPayPalUnified'")
            ->orWhere("pm.name = 'SwagPaymentPayPalUnifiedInstallments'")
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
