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

namespace SwagPaymentPayPalUnified\Components\Services\Plus;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Routing\Router;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\PaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ShipmentDetails;

class PlusPaymentBuilderService extends PaymentBuilderService
{
    const EDD_ATTRIBUTE_COLUMN_NAME = 'swag_paypal_estimated_delivery_date_days';
    /**
     * @var CrudService
     */
    private $attributeService;

    /**
     * @param Router                   $router
     * @param SettingsServiceInterface $settingsService
     * @param CrudService              $crudService
     */
    public function __construct(Router $router, SettingsServiceInterface $settingsService, CrudService $crudService)
    {
        parent::__construct($router, $settingsService);

        $this->attributeService = $crudService;
    }

    /**
     * @param PaymentBuilderParameters $params
     *
     * @return Payment
     */
    public function getPayment(PaymentBuilderParameters $params)
    {
        $payment = parent::getPayment($params);
        $payment->getRedirectUrls()->setReturnUrl($this->getReturnUrl());
        $payment->getTransactions()->setShipmentDetails($this->getShipmentDetails($params->getBasketData()));

        return $payment;
    }

    /**
     * @return false|string
     */
    private function getReturnUrl()
    {
        return $this->router->assemble([
            'action' => 'return',
            'controller' => 'PaypalUnified',
            'forceSecure' => true,
            'basketId' => BasketIdWhitelist::WHITELIST_IDS['PayPalPlus'],
        ]);
    }

    /**
     * @param array $basketData
     *
     * @return ShipmentDetails
     */
    private function getShipmentDetails(array $basketData)
    {
        $eddValue = $this->getEddValue($basketData);
        if ($eddValue === null) {
            return null;
        }

        $shipmentDetails = new ShipmentDetails();
        $shipmentDetails->setEstimatedDeliveryDate($eddValue);

        return $shipmentDetails;
    }

    /**
     * @param array $basketData
     *
     * @return null|string
     */
    private function getEddValue(array $basketData)
    {
        $attribute = $this->attributeService->get('s_articles_attributes', self::EDD_ATTRIBUTE_COLUMN_NAME);

        if ($attribute === null) {
            return null;
        }

        $maxDeliveryDays = 0;
        foreach ($basketData['content'] as $basketItem) {
            $estimatedDeliveryDateDays = (int) $basketItem['additional_details'][self::EDD_ATTRIBUTE_COLUMN_NAME];

            if ($estimatedDeliveryDateDays > 0 && $estimatedDeliveryDateDays > $maxDeliveryDays) {
                $maxDeliveryDays = $estimatedDeliveryDateDays;
            }
        }

        if ($maxDeliveryDays === 0) {
            return null;
        }

        //Calculate the absolute delivery date by adding the days from the product attribute
        $date = new \DateTime();
        $date->add(new \DateInterval('P' . $maxDeliveryDays . 'D'));

        return $date->format('Y-m-d');
    }
}
