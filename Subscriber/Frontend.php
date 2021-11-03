<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_View_Default;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\RiskManagement\RiskManagementInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class Frontend implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var RiskManagementInterface
     */
    private $riskManagement;

    /**
     * @param string $pluginDir
     */
    public function __construct(
        $pluginDir,
        SettingsServiceInterface $settingsService,
        Connection $connection,
        RiskManagementInterface $riskManagement
    ) {
        $this->pluginDir = $pluginDir;
        $this->settingsService = $settingsService;
        $this->connection = $connection;
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->riskManagement = $riskManagement;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJavascript',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'onLoadAjaxListing',
            'Theme_Inheritance_Template_Directories_Collected' => 'onCollectTemplateDir',
        ];
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectJavascript()
    {
        $jsPath = [
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-confirm.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall-shipping-payment.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.payment-wall.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.custom-shipping-payment.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.express-checkout-button-in-context.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.in-context-checkout.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.smart-payment-buttons.js',
            $this->pluginDir . '/Resources/views/frontend/_public/src/js/jquery.swag-paypal-unified.installments-banner.js',
        ];

        return new ArrayCollection($jsPath);
    }

    public function onLoadAjaxListing(\Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();
        if ($controller->Request()->getActionName() !== 'listingCount') {
            return;
        }

        $category = $controller->Request()->getParam('sCategory');

        $controller->View()->assign('paypalIsNotAllowed', $this->riskManagement->isPayPalNotAllowed(null, $category));
    }

    public function onPostDispatchSecure(\Enlight_Controller_ActionEventArgs $args)
    {
        if (!$this->settingsService->hasSettings()) {
            return;
        }

        $active = (bool) $this->settingsService->get('active');
        if (!$active) {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        $showPayPalLogo = $swUnifiedActive && (bool) $this->settingsService->get('show_sidebar_logo');

        /** @var Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        $productId = $args->getSubject()->Request()->getParam('sArticle');
        $category = $args->getSubject()->Request()->getParam('sCategory');

        //Assign shop specific and configurable values to the view.
        $view->assign('paypalUnifiedShowLogo', $showPayPalLogo);

        if (!$this->shouldCheckRiskManagement($args)) {
            return;
        }

        $view->assign('paypalIsNotAllowed', $this->riskManagement->isPayPalNotAllowed($productId, $category));
    }

    /**
     * Adds the template directory to the TemplateManager
     */
    public function onCollectTemplateDir(\Enlight_Event_EventArgs $args)
    {
        $dirs = $args->getReturn();
        $dirs[] = $this->pluginDir . '/Resources/views/';

        $args->setReturn($dirs);
    }

    /**
     * @return bool
     */
    private function shouldCheckRiskManagement(\Enlight_Controller_ActionEventArgs $args)
    {
        $controllerName = $args->getSubject()->Request()->getControllerName();
        $actionName = $args->getSubject()->Request()->getActionName();

        $rejectedActionList = [
            'edit',
            'ajaxSelection',
            'ajaxSave',
            'handleExtra',
        ];

        if ($controllerName === 'address' && \in_array($actionName, $rejectedActionList, true)) {
            return false;
        }

        return true;
    }
}
