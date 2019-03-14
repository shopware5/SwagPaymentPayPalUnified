<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

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
        return $this->router->assemble([
            'module' => 'widgets',
            'controller' => 'PaypalUnifiedExpressCheckout',
            'action' => 'expressCheckoutReturn',
            'forceSecure' => true,
            'basketId' => BasketIdWhitelist::WHITELIST_IDS['PayPalExpress'], //PayPal Express Checkout basket Id
        ]);
    }

    /**
     * @return false|string
     */
    private function getCancelUrl()
    {
        return $this->router->assemble(
            [
                'controller' => 'checkout',
                'action' => 'cart',
                'forceSecure' => true,
            ]
        );
    }
}
