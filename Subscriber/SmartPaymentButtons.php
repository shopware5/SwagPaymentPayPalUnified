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
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class SmartPaymentButtons implements SubscriberInterface
{
    /**
     * @var SettingsService
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

    public function __construct(SettingsServiceInterface $settingsService, Connection $connection)
    {
        $this->settingsService = $settingsService;
        $this->connection = $connection;
        $this->paymentMethodProvider = new PaymentMethodProvider();
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addSmartPaymentButtons',
        ];
    }

    public function addSmartPaymentButtons(ActionEventArgs $args)
    {
        $request = $args->getRequest();
        $view = $args->getSubject()->View();

        if (strtolower($request->getActionName()) !== 'confirm') {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();

        if ($generalSettings === null
            || !$generalSettings->getUseSmartPaymentButtons()
            || $generalSettings->getMerchantLocation() === GeneralSettingsModel::MERCHANT_LOCATION_GERMANY
        ) {
            return;
        }

        $view->assign('paypalUnifiedUseSmartPaymentButtons', true);
        $view->assign('paypalUnifiedSpbClientId', $generalSettings->getClientId());
        $view->assign('paypalUnifiedSpbCurrency', $view->getAssign('sBasket')['sCurrencyName']);
        $view->assign('paypalUnifiedPaymentId', $this->paymentMethodProvider->getPaymentId($this->connection));
    }
}
