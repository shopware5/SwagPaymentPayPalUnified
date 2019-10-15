<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

use Shopware\Components\Cart\PaymentTokenService;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class ExpressCheckoutPaymentBuilderService extends PaymentBuilderService
{
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

        //Since we used the sBasket module earlier, the currencies might not be available,
        //but paypal needs them.
        if (!$payment->getTransactions()->getAmount()->getCurrency()) {
            $payment->getTransactions()->getAmount()->setCurrency($currency);
        }

        if ($payment->getTransactions()->getItemList() !== null) {
            foreach ($payment->getTransactions()->getItemList()->getItems() as $item) {
                if (!$item->getCurrency()) {
                    $item->setCurrency($currency);
                }
            }
        }

        return $payment;
    }

    /**
     * @return false|string
     */
    private function getReturnUrl()
    {
        $routingParameters = [
            'module' => 'frontend',
            'controller' => 'PaypalUnifiedExpressCheckout',
            'action' => 'expressCheckoutReturn',
            'forceSecure' => true,
            'basketId' => BasketIdWhitelist::WHITELIST_IDS['PayPalExpress'], //PayPal Express Checkout basket Id
        ];

        // Shopware 5.6+ supports session restoring
        $token = $this->requestParams->getPaymentToken();
        if ($token !== null) {
            $routingParameters[PaymentTokenService::TYPE_PAYMENT_TOKEN] = $token;
        }

        return $this->router->assemble($routingParameters);
    }

    /**
     * @return false|string
     */
    private function getCancelUrl()
    {
        $routingParameters = [
            'controller' => 'checkout',
            'action' => 'cart',
            'forceSecure' => true,
        ];

        // Shopware 5.6+ supports session restoring
        $token = $this->requestParams->getPaymentToken();
        if ($token !== null) {
            $routingParameters['swPaymentToken'] = $token;
        }

        return $this->router->assemble($routingParameters);
    }
}
