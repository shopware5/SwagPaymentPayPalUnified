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
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Models\Settings\Plus;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class Account implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    public function __construct(
        Connection $connection,
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider
    ) {
        $this->connection = $connection;
        $this->settingsService = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
        $this->paymentMethodProvider = new PaymentMethodProvider();
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
        /** @var \Shopware_Controllers_Frontend_Account $controller */
        $controller = $args->getSubject();
        $allowedActions = ['index', 'payment'];
        $action = $controller->Request()->getActionName();

        if (!in_array($action, $allowedActions, true)) {
            return;
        }

        $shop = $this->dependencyProvider->getShop();
        if ($shop === null) {
            return;
        }

        $swUnifiedActive = $this->paymentMethodProvider->getPaymentMethodActiveFlag($this->connection);
        if (!$swUnifiedActive) {
            return;
        }

        $shopId = $shop->getId();
        /** @var Plus|null $plusSettings */
        $plusSettings = $this->settingsService->getSettings($shopId, SettingsTable::PLUS);

        if ($plusSettings === null || !$plusSettings->getActive()) {
            return;
        }

        $paymentName = $plusSettings->getPaymentName();
        $paymentDescription = $plusSettings->getPaymentDescription();

        if ($paymentName === '' || $paymentName === null) {
            return;
        }

        $view = $controller->View();
        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId($this->connection);

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
