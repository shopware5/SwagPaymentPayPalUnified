<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs as ActionEventArgs;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Models\Settings\Plus;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class Account implements SubscriberInterface
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
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @param ModelManager             $modelManager
     * @param Connection               $connection
     * @param SettingsServiceInterface $settingsService
     * @param DependencyProvider       $dependencyProvider
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        SettingsServiceInterface $settingsService,
        DependencyProvider $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->connection = $connection;
        $this->settingsService = $settingsService;
        $this->dependencyProvider = $dependencyProvider;
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

    /**
     * @param ActionEventArgs $args
     */
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

        $shopId = $shop->getId();
        /** @var Plus $plusSettings */
        $plusSettings = $this->settingsService->getSettings($shopId, SettingsTable::PLUS);

        if ($plusSettings === null || !$plusSettings->getActive()) {
            return;
        }

        $view = $controller->View();
        $paymentMethodProvider = new PaymentMethodProvider($this->modelManager);
        $unifiedPaymentId = $paymentMethodProvider->getPaymentId($this->connection);

        $customerData = $view->getAssign('sUserData');
        $customerPayment = $customerData['additional']['payment'];

        if ((int) $customerPayment['id'] === $unifiedPaymentId) {
            $customerPayment['description'] = $plusSettings->getPaymentName();
            $customerPayment['additionaldescription'] .= '<br>' . $plusSettings->getPaymentDescription();

            $customerData['additional']['payment'] = $customerPayment;
            $view->assign('sUserData', $customerData);
        }

        $paymentMethods = $view->getAssign('sPaymentMeans');

        foreach ($paymentMethods as &$paymentMethod) {
            if ((int) $paymentMethod['id'] === $unifiedPaymentId) {
                $paymentMethod['description'] = $plusSettings->getPaymentName();
                $paymentMethod['additionaldescription'] .= '<br>' . $plusSettings->getPaymentDescription();
                break;
            }
        }
        unset($paymentMethod);

        $view->assign('sPaymentMeans', $paymentMethods);
    }
}
