<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Common\CartHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class ExpressCheckoutPaymentBuilderService extends PaymentBuilderService
{
    public function __construct(
        SettingsServiceInterface $settingsService,
        SnippetManager $snippetManager,
        DependencyProvider $dependencyProvider,
        PriceFormatter $priceFormatter,
        CustomerHelper $customerHelper,
        CartHelper $cartHelper,
        ReturnUrlHelper $returnUrlHelper
    ) {
        parent::__construct(
            $settingsService,
            $snippetManager,
            $dependencyProvider,
            $priceFormatter,
            $customerHelper,
            $cartHelper,
            $returnUrlHelper
        );
    }

    /**
     * @param string|null $currency
     *
     * @return Payment
     */
    public function getPayment(PaymentBuilderParameters $params, $currency = null)
    {
        $payment = parent::getPayment($params);

        $redirectUrls = $payment->getRedirectUrls();
        $redirectUrls->setReturnUrl($this->getReturnUrl());
        $redirectUrls->setCancelUrl($this->getCancelUrl());

        // Since we used the sBasket module earlier, the currencies might not be available but PayPal needs them.
        if (!$payment->getTransactions()->getAmount()->getCurrency() && \is_string($currency)) {
            $payment->getTransactions()->getAmount()->setCurrency($currency);
        }

        if ($payment->getTransactions()->getItemList() !== null) {
            foreach ($payment->getTransactions()->getItemList()->getItems() as $item) {
                if (!$item->getCurrency() && \is_string($currency)) {
                    $item->setCurrency($currency);
                }
            }
        }

        return $payment;
    }

    /**
     * @return string
     */
    private function getReturnUrl()
    {
        return $this->returnUrlHelper->getReturnUrl(
            BasketIdWhitelist::WHITELIST_IDS['PayPalExpress'], //PayPal Express Checkout basket Id,
            $this->requestParams->getPaymentToken(),
            [
                'controller' => 'PaypalUnifiedExpressCheckout',
                'action' => 'expressCheckoutReturn',
            ]
        );
    }

    /**
     * @return string
     */
    private function getCancelUrl()
    {
        return $this->returnUrlHelper->getCancelUrl(
            null,
            $this->requestParams->getPaymentToken(),
            [
                'controller' => 'checkout',
                'action' => 'cart',
            ]
        );
    }
}
