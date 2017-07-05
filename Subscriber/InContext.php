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
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class InContext implements SubscriberInterface
{
    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @param ModelManager             $modelManager
     * @param Connection               $connection
     * @param SettingsServiceInterface $settingsService
     */
    public function __construct(
        ModelManager $modelManager,
        Connection $connection,
        SettingsServiceInterface $settingsService
    ) {
        $this->paymentMethodProvider = new PaymentMethodProvider($modelManager);
        $this->connection = $connection;
        $this->settingsService = $settingsService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['addInContextButton'],
                ['addInContextInfoToRequest', 100],
            ],
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addInContextButton(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $controller */
        $controller = $args->getSubject();
        $action = $controller->Request()->getActionName();

        if ($action !== 'confirm') {
            return;
        }

        $settings = $this->settingsService->getSettings();

        if (!$settings || !$settings->getActive() || !$settings->getUseInContext()) {
            return;
        }

        $view = $controller->View();
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->connection));
        $view->assign('paypalUnifiedModeSandbox', $settings->getSandbox());
        $view->assign('paypalUnifiedUseInContext', $settings->getUseInContext());
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addInContextInfoToRequest(\Enlight_Controller_ActionEventArgs $args)
    {
        $request = $args->getRequest();
        if ($request->getActionName() === 'payment' &&
            $request->getParam('useInContext') &&
            $args->getResponse()->isRedirect()
        ) {
            $args->getSubject()->redirect([
                'controller' => 'PaypalUnified',
                'action' => 'gateway',
                'useInContext' => true,
            ]);
        }
    }
}
