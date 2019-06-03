<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\StateTranslatorService;
use Shopware\Models\Order\Order;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class Shopware_Controllers_Backend_PaypalUnifiedGeneralSettings extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = GeneralSettingsModel::class;

    /**
     * {@inheritdoc}
     */
    protected $alias = 'general';

    public function detailAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId');

        /** @var SettingsServiceInterface $settingsService */
        $settingsService = $this->get('paypal_unified.settings_service');

        /** @var GeneralSettingsModel $settings */
        $settings = $settingsService->getSettings($shopId);

        if ($settings !== null) {
            $this->view->assign('general', $settings->toArray());
        }
    }

    public function getPaymentStateAction()
    {
        // inspired by Shopware_Controllers_Backend_Order::loadListAction
        $em = $this->container->get('models');
        $paymentState = $em->getRepository(Order::class)->getPaymentStatusQuery()->getArrayResult();

        $stateTranslator = $this->get('shopware.components.state_translator');

        $paymentState = array_map(function ($paymentStateItem) use ($stateTranslator) {
            $paymentStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);

            return $paymentStateItem;
        }, $paymentState);

        $this->View()->assign([
            'success' => true,
            'data' => $paymentState,
            'total' => count($paymentState)
        ]);
    }
}
