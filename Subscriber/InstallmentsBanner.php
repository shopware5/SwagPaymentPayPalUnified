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
use Enlight_Controller_Request_Request as Request;
use Enlight_View_Default as View;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class InstallmentsBanner implements SubscriberInterface
{
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
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        Connection $connection,
        ContextServiceInterface $contextService
    ) {
        $this->settingsService = $settingsService;
        $this->connection = $connection;
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->contextService = $contextService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatchSecure',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets' => 'onPostDispatchSecure',
        ];
    }

    public function onPostDispatchSecure(ActionEventArgs $args)
    {
        if (!$this->settingsService->hasSettings()) {
            return;
        }

        $active = (bool) $this->settingsService->get('active');
        if (!$active) {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        $advertiseInstallments = $swUnifiedActive && (bool) $this->settingsService->get('advertise_installments', SettingsTable::INSTALLMENTS);
        if ($advertiseInstallments === false) {
            return;
        }

        /** @var View $view */
        $view = $args->getSubject()->View();

        $clientId = $this->settingsService->get('client_id');
        $amount = $this->getAmountForPage($args->getRequest(), $view);
        $currency = $this->contextService->getShopContext()->getCurrency()->getCurrency();

        $view->assign('paypalUnifiedInstallmentsBanner', $advertiseInstallments);
        $view->assign('paypalUnifiedInstallmentsBannerClientId', $clientId);
        $view->assign('paypalUnifiedInstallmentsBannerAmount', $amount);
        $view->assign('paypalUnifiedInstallmentsBannerCurrency', $currency);
    }

    /**
     * @return float
     */
    private function getAmountForPage(Request $request, View $view)
    {
        $amount = 0.0;
        $controllerName = strtolower($request->getControllerName());
        $actionName = strtolower($request->getActionName());
        $validCheckoutActions = ['cart', 'ajaxcart', 'ajax_cart'];

        if ($controllerName === 'detail' && $actionName === 'index') {
            $product = $view->getAssign('sArticle');
            $amount = (float) $product['price_numeric'];
        } elseif ($controllerName === 'checkout' && in_array($actionName, $validCheckoutActions, true)) {
            $cart = $view->getAssign('sBasket');
            $amount = (float) $cart['AmountNumeric'];
        }

        return $amount;
    }
}
