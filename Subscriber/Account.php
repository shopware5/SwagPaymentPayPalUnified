<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Models\Shop\Shop;
use Shopware_Controllers_Frontend_Account;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Models\Settings\Plus;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use UnexpectedValueException;

class Account implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    public function __construct(
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider,
        PaymentMethodProviderInterface $paymentMethodProvider
    ) {
        $this->settingsService = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
        $this->paymentMethodProvider = $paymentMethodProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'onPostDispatchAccount',
        ];
    }

    public function onPostDispatchAccount(ActionEventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Account $controller */
        $controller = $args->getSubject();
        $allowedActions = ['index', 'payment'];
        $action = $controller->Request()->getActionName();

        if (!\in_array($action, $allowedActions, true)) {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        if (!$swUnifiedActive) {
            return;
        }

        $shop = $this->dependencyProvider->getShop();

        if (!$shop instanceof Shop) {
            throw new UnexpectedValueException(sprintf('Tried to access %s, but it\'s not set in the DIC.', Shop::class));
        }

        $shopId = $shop->getId();

        /** @var Plus|null $plusSettings */
        $plusSettings = $this->settingsService->getSettings($shopId, SettingsTable::PLUS);
        if (!$plusSettings instanceof Plus || !$plusSettings->getActive()) {
            return;
        }

        $paymentName = $plusSettings->getPaymentName();
        $paymentDescription = $plusSettings->getPaymentDescription();

        if ($paymentName === '' || $paymentName === null) {
            return;
        }

        $view = $controller->View();
        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);

        $customerData = $view->getAssign('sUserData');
        $customerPayment = $customerData['additional']['payment'];

        if ((int) $customerPayment['id'] === $unifiedPaymentId) {
            $customerPayment['description'] = $paymentName;
            $customerPayment['additionaldescription'] .= '<br>' . $paymentDescription;

            $customerData['additional']['payment'] = $customerPayment;
            $view->assign('sUserData', $customerData);
        }

        $paymentMethods = $view->getAssign('sPaymentMeans');

        foreach ($paymentMethods as &$paymentMethod) {
            if ((int) $paymentMethod['id'] === $unifiedPaymentId) {
                $paymentMethod['description'] = $paymentName;
                $paymentMethod['additionaldescription'] .= '<br>' . $paymentDescription;
                break;
            }
        }
        unset($paymentMethod);

        $view->assign('sPaymentMeans', $paymentMethods);
    }
}
