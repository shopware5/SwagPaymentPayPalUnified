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
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;

class Uninstaller
{
    /**
     * @var CrudService|CrudServiceInterface
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
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @param CrudService|CrudServiceInterface $attributeCrudService
     */
    public function __construct(
        $attributeCrudService,
        ModelManager $modelManager,
        Connection $connection,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->attributeCrudService = $attributeCrudService;
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * @param bool $safeMode
     *
     * @return void
     */
    public function uninstall($safeMode)
    {
        $this->deactivatePayments();
        $this->removeAttributes();

        if (!$safeMode) {
            $this->removeSettingsTables();
        }
    }

    /**
     * @return void
     */
    private function deactivatePayments()
    {
        foreach (PaymentMethodProvider::getAllUnifiedNames() as $paymentMethodName) {
            $this->paymentMethodProvider->setPaymentMethodActiveFlag($paymentMethodName, false);
        }
    }

    /**
     * @return void
     */
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
        $this->modelManager->generateAttributeModels(['s_core_paymentmeans_attributes']);
    }

    /**
     * @return void
     */
    private function removeSettingsTables()
    {
        $sql = 'DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_express`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_general`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_installments`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_plus`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_advanced_credit_debit_card`;
                DROP TABLE IF EXISTS `swag_payment_paypal_unified_settings_pay_upon_invoice`;';

        $this->connection->exec($sql);
    }
}
