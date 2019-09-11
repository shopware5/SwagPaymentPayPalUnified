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

class Uninstaller
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

    public function __construct(CrudService $attributeCrudService, ModelManager $modelManager, Connection $connection)
    {
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
    }

    /**
     * @param bool $safeMode
     */
    public function uninstall($safeMode)
    {
        $this->deactivatePayments();
        $this->removeAttributes();

        if (!$safeMode) {
            $this->removeSettingsTables();
        }
    }

    private function deactivatePayments()
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->modelManager);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
    }

    private function removeAttributes()
    {
        if ($this->attributeCrudService->get('s_core_paymentmeans_attributes', 'swag_paypal_unified_display_in_plus_iframe') !== null) {
            $this->attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'swag_paypal_unified_display_in_plus_iframe'
            );
        }
        if ($this->attributeCrudService->get('s_core_paymentmeans_attributes', 'swag_paypal_unified_plus_iframe_payment_logo') !== null) {
            $this->attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'swag_paypal_unified_plus_iframe_payment_logo'
            );
        }

        if ($this->attributeCrudService->get('s_articles_attributes', 'swag_paypal_unified_express_disabled') !== null) {
            $this->attributeCrudService->delete(
                's_articles_attributes',
                'swag_paypal_unified_express_disabled'
            );
        }
        $this->modelManager->generateAttributeModels(['s_core_paymentmeans_attributes', 's_articles_attributes']);
    }

    private function removeSettingsTables()
    {
        $sql = 'DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_express`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_general`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_installments`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_plus`;';

        $this->connection->exec($sql);
    }
}
