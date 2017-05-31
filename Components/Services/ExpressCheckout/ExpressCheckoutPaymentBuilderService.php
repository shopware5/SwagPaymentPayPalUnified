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

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;

class ExpressCheckoutPaymentBuilderService extends PaymentBuilderService
{
    /**
     * @param PaymentBuilderParameters $params
     * @param string|null              $currency
     *
     * @return Payment
     */
    public function getPayment(PaymentBuilderParameters $params, $currency = null)
    {
        $payment = parent::getPayment($params);

        $payment->getRedirectUrls()->setReturnUrl($this->getReturnUrl());
        $payment->getRedirectUrls()->setCancelUrl($this->getCancelUrl());

        //Since we used the sBasket module earlier, the currencies might not be available,
        //but paypal needs them.
        if (!$payment->getTransactions()->getAmount()->getCurrency()) {
            $payment->getTransactions()->getAmount()->setCurrency($currency);
        }

        foreach ($payment->getTransactions()->getItemList()->getItems() as $item) {
            if (!$item->getCurrency()) {
                $item->setCurrency($currency);
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
