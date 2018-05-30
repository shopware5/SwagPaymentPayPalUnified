<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Setup\Installer;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SwagPaymentPayPalUnified extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('paypal_unified.plugin_dir', $this->getPath());
        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context)
    {
        $installer = new Installer(
            $this->container->get('models'),
            $this->container->get('dbal_connection'),
            $this->container->get('shopware_attribute.crud_service'),
            $this->getPath()
        );

        $installer->install();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context)
    {
        $modelManager = $this->container->get('models');
        $paymentMethodProvider = new PaymentMethodProvider($modelManager);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        /** @var CrudService $attributeCrudService */
        $attributeCrudService = $this->container->get('shopware_attribute.crud_service');

        if ($attributeCrudService->get('s_core_paymentmeans_attributes', 'swag_paypal_unified_display_in_plus_iframe') !== null) {
            $attributeCrudService->delete(
                's_core_paymentmeans_attributes',
                'swag_paypal_unified_display_in_plus_iframe'
            );
            $modelManager->generateAttributeModels(['s_core_paymentmeans_attributes']);
        }

        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(UpdateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(true);
        $paymentMethodProvider->setPaymentMethodActiveFlag(true, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));
        $paymentMethodProvider->setPaymentMethodActiveFlag(false);
        $paymentMethodProvider->setPaymentMethodActiveFlag(false, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);

        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);
    }
}
