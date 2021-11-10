<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Plus;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
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
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ShipmentDetails;

class PlusPaymentBuilderService extends PaymentBuilderService
{
    const EDD_ATTRIBUTE_COLUMN_NAME = 'swag_paypal_estimated_delivery_date_days';

    /**
     * @var CrudService
     */
    private $attributeService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        CrudService $crudService,
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

        $this->attributeService = $crudService;
    }

    /**
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
     * @return string
     */
    private function getReturnUrl()
    {
        return $this->returnUrlHelper->getReturnUrl(
            BasketIdWhitelist::WHITELIST_IDS['PayPalPlus'],
            $this->requestParams->getPaymentToken(),
            ['plus' => true, 'controller' => 'PaypalUnified']
        );
    }

    /**
     * @return ShipmentDetails|null
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
     * @return string|null
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
