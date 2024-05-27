<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Enlight_View_Default;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Services\NonceService;
use SwagPaymentPayPalUnified\Setup\TranslationUpdater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Backend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var NonceService
     */
    private $nonceService;

    /**
     * @var Shopware_Components_Translation
     */
    private $translationWriter;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param string $pluginDir
     */
    public function __construct(
        $pluginDir,
        NonceService $nonceService,
        ContainerInterface $container,
        Connection $connection
    ) {
        $this->pluginDir = $pluginDir;
        $this->nonceService = $nonceService;
        $this->connection = $connection;
        $this->container = $container;

        $this->translationWriter = $this->getTranslator();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onLoadBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Config' => 'onPostDispatchConfig',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Payment' => 'onPostDispatchPayment',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatchOrder',
        ];
    }

    /**
     * Handles the Enlight_Controller_Action_PostDispatchSecure_Backend_Index event.
     * Extends the backend icon set by the paypal icon.
     *
     * @return void
     */
    public function onLoadBackendIndex(ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $request = $args->getRequest();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');
        $view->extendsTemplate('backend/paypal_unified/menu_icon.tpl');

        if ($request->getActionName() === 'index' && $request->getParam('file') === 'app') {
            foreach (['sellerNonceSandbox', 'sellerNonceLive'] as $nonceType) {
                $view->assign($nonceType, $this->nonceService->getBase64UrlEncodedRandomNonce());
            }

            $view->extendsTemplate('backend/paypal_unified_settings/mixin/onboarding_helper.js');
        }
    }

    /**
     * @return void
     */
    public function onPostDispatchConfig(ActionEventArgs $arguments)
    {
        $view = $arguments->getSubject()->View();
        $request = $arguments->getSubject()->Request();

        if ($request->getActionName() === 'load') {
            $view->addTemplateDir($this->pluginDir . '/Resources/views/');
            $view->extendsTemplate('backend/config/view/form/document_paypal_unified.js');
        }

        if ($request->getActionName() === 'saveValues' && $request->getParam('_repositoryClass') === 'shop') {
            $translationUpdater = new TranslationUpdater($this->connection, $this->translationWriter);
            $translationUpdater->updateTranslationByLocaleId($request->getParam('localeId', 1));
        }
    }

    /**
     * @return void
     */
    public function onPostDispatchPayment(ActionEventArgs $args)
    {
        /** @var Enlight_View_Default $view */
        $view = $args->get('subject')->View();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');

        if ($args->get('request')->getActionName() === 'load') {
            $view->assign('deactivatedPaymentMethods', PaymentMethodProvider::getDeactivatedPaymentMethods());
            $view->extendsTemplate('backend/payment/controller/payment_paypal_unified.js');
        }
    }

    /**
     * @return void
     */
    public function onPostDispatchOrder(ActionEventArgs $arguments)
    {
        /** @var Enlight_View_Default $view */
        $view = $arguments->get('subject')->View();

        $view->addTemplateDir($this->pluginDir . '/Resources/views/');
        $view->assign('paypalPaymentMethodNames', json_encode(PaymentMethodProvider::getAllUnifiedNames()));

        if ($arguments->getRequest()->getActionName() !== 'load') {
            return;
        }

        $view->extendsTemplate('backend/order/detail/overview_paypal_extension.js');
    }

    /**
     * @return Shopware_Components_Translation
     */
    private function getTranslator()
    {
        $translation = null;

        if ($this->container->initialized('translation')) {
            $translation = $this->container->get('translation');
        }

        if (!$translation instanceof Shopware_Components_Translation) {
            $translation = new Shopware_Components_Translation($this->connection, $this->container);
        }

        return $translation;
    }
}
