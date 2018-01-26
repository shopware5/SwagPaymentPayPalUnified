<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

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
