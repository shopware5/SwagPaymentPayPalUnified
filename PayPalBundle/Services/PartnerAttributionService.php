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

namespace SwagPaymentPayPalUnified\PayPalBundle\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class PartnerAttributionService
{
    /**
     * The Partner-Attribution-Id for the PayPal Plus integration
     */
    const PARTNER_ID_PAYPAL_PLUS = 'Shopware_Cart_Plus_2';

    /**
     * The Partner-Attribution-Id for the PayPal Classic integration
     */
    const PARTNER_ID_PAYPAL_CLASSIC = 'Shopware_Cart_EC_2';

    /**
     * @var SettingsServiceInterface
     */
    private $config;

    /**
     * @param SettingsServiceInterface $config
     */
    public function __construct(SettingsServiceInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getPartnerAttributionId()
    {
        $paymentType = $this->getCurrentPaymentType();

        return $paymentType === PaymentType::PAYPAL_PLUS ? self::PARTNER_ID_PAYPAL_PLUS : self::PARTNER_ID_PAYPAL_CLASSIC;
    }

    /**
     * @return int
     */
    private function getCurrentPaymentType()
    {
        $usePayPalPlus = (bool) $this->config->get('plus_active');

        return $usePayPalPlus === true ? PaymentType::PAYPAL_PLUS : PaymentType::PAYPAL_CLASSIC;
    }
}
