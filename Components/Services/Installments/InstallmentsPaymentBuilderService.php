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

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;

class InstallmentsPaymentBuilderService extends PaymentBuilderService
{
    /**
     * {@inheritdoc}
     */
    public function getPayment(PaymentBuilderParameters $params)
    {
        $payment = parent::getPayment($params);

        $payment->getPayer()->setExternalSelectedFundingInstrumentType('CREDIT');
        $payment->getRedirectUrls()->setReturnUrl($this->getReturnUrl());

        switch ($this->settings->get('paypal_payment_intent')) {
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
