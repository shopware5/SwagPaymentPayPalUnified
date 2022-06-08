<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Controllers_Frontend_Account;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class PayLaterMessage implements SubscriberInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(SettingsServiceInterface $settingsService, ContextServiceInterface $contextService)
    {
        $this->settingsService = $settingsService;
        $this->contextService = $contextService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Account' => 'showPayLaterMessage',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'showPayLaterMessage',
        ];
    }

    /**
     * @return void
     */
    public function showPayLaterMessage(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Account $subject */
        $subject = $args->get('subject');

        $action = $subject->Request()->getActionName();
        if (!\in_array($action, ['shippingPayment', 'payment'])) {
            return;
        }

        /** @var GeneralSettingsModel|null $generalSettings */
        $generalSettings = $this->settingsService->getSettings();
        if (!$generalSettings || !$generalSettings->getActive()) {
            return;
        }

        $clientId = $generalSettings->getSandbox() ? $generalSettings->getSandboxClientId() : $generalSettings->getClientId();

        $subject->View()->assign([
            'payLaterMesssage' => true,
            'payLaterMesssageClientId' => $clientId,
            'payLaterMessageCurrency' => $this->contextService->getContext()->getCurrency()->getCurrency(),
        ]);
    }
}
