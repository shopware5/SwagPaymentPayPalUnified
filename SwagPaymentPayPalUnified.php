<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\DependencyInjection\OrderHandlerCompilerPass;
use SwagPaymentPayPalUnified\Components\DependencyInjection\OrderToArrayHandlerCompilerPass;
use SwagPaymentPayPalUnified\Components\DependencyInjection\PaymentSourceHandlerCompilerPass;
use SwagPaymentPayPalUnified\Components\DependencyInjection\PaymentSourceValueHandlerCompilerPass;
use SwagPaymentPayPalUnified\Components\DependencyInjection\RiskManagementValidatorCompilerPass;
use SwagPaymentPayPalUnified\Components\DependencyInjection\RiskManagementValueCompilerPass;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\Installer;
use SwagPaymentPayPalUnified\Setup\PaymentModels\PaymentModelFactory;
use SwagPaymentPayPalUnified\Setup\Uninstaller;
use SwagPaymentPayPalUnified\Setup\Updater;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SwagPaymentPayPalUnified extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('paypal_unified.plugin_dir', $this->getPath());

        $container->addCompilerPass(new OrderHandlerCompilerPass());
        $container->addCompilerPass(new PaymentSourceHandlerCompilerPass());
        $container->addCompilerPass(new PaymentSourceValueHandlerCompilerPass());
        $container->addCompilerPass(new RiskManagementValueCompilerPass());
        $container->addCompilerPass(new RiskManagementValidatorCompilerPass());
        $container->addCompilerPass(new OrderToArrayHandlerCompilerPass());

        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        $translation = $this->container->initialized('translation')
            ? $this->container->get('translation')
            : new Shopware_Components_Translation($this->container->get('dbal_connection'), $this->container);

        $installer = new Installer(
            $this->container->get('models'),
            $this->container->get('dbal_connection'),
            $this->container->get('shopware_attribute.crud_service'),
            $translation,
            $this->getPaymentMethodProvider(),
            $this->getPaymentModelFactory(),
            $this->getPath()
        );

        $installer->install();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        $uninstaller = new Uninstaller(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models'),
            $this->container->get('dbal_connection'),
            $this->getPaymentMethodProvider()
        );
        $uninstaller->uninstall($context->keepUserData());

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {
        $updater = new Updater(
            $this->container->get('shopware_attribute.crud_service'),
            $this->container->get('models'),
            $this->container->get('dbal_connection'),
            $this->getPaymentMethodProvider(),
            $this->getPaymentModelFactory()
        );
        $updater->update($context->getCurrentVersion());

        $context->scheduleClearCache(UpdateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $this->getPaymentMethodProvider()->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, true);
        $this->getPaymentMethodProvider()->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME, true);

        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $this->getPaymentMethodProvider()->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME, false);
        $this->getPaymentMethodProvider()->setPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME, false);

        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }

    private function getPaymentMethodProvider()
    {
        return new PaymentMethodProvider(
            $this->container->get('dbal_connection'),
            $this->container->get('models')
        );
    }

    private function getPaymentModelFactory()
    {
        return new PaymentModelFactory();
    }
}
