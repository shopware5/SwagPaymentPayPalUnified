<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use Shopware\Components\Routing\RouterInterface;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class InstallmentsPaymentBuilderService extends PaymentBuilderService
{

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(
        RouterInterface $router,
        SettingsServiceInterface $settingsService,
        SnippetManager $snippetManager,
        dependencyProvider $dependencyProvider
    ) {
        parent::__construct($router, $settingsService, $snippetManager, $dependencyProvider);

        $this->dependencyProvider = $dependencyProvider;

    }


    /**
     * {@inheritdoc}
     */
    public function getPayment(PaymentBuilderParameters $params)
    {
        $payment = parent::getPayment($params);

        $payment->getPayer()->setExternalSelectedFundingInstrumentType('CREDIT');
        $payment->getRedirectUrls()->setReturnUrl($this->getReturnUrl());

        switch ($this->settings->get('intent', SettingsTable::INSTALLMENTS)) {
            case 0:
                $payment->setIntent('sale');
                break;
            case 1: //Overwrite "authentication"
            case 2:
                $payment->setIntent('order');
                break;
        }

        return $payment;
    }

    /**
     * @return false|string
     */
    private function getReturnUrl()
    {
        $token = $this->dependencyProvider->getToken();

        if ($token) {
            return $this->router->assemble([
                'controller' => 'PaypalUnifiedInstallments',
                'action' => 'return',
                'forceSecure' => true,
                'basketId' => $this->requestParams->getBasketUniqueId(),
                'swPaymentToken' => $token,
            ]);
        }

        if ($this->requestParams->getBasketUniqueId()) {
            return $this->router->assemble([
                'controller' => 'PaypalUnifiedInstallments',
                'action' => 'return',
                'forceSecure' => true,
                'basketId' => $this->requestParams->getBasketUniqueId(),
            ]);
        }

        return $this->router->assemble([
            'controller' => 'PaypalUnifiedInstallments',
            'action' => 'return',
            'forceSecure' => true,
        ]);
    }
}
