<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\ExpressCheckout;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Plus\PlusPaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\SettingsServicePaymentBuilderServiceMock;

class PlusPaymentBuilderServiceTest extends TestCase
{
    public function test_serviceIsAvailable()
    {
        $service = Shopware()->Container()->get('paypal_unified.plus.payment_builder_service');
        static::assertSame(PlusPaymentBuilderService::class, get_class($service));
    }

    public function test_getPayment_has_plus_basketId()
    {
        $request = $this->getRequestData();

        static::assertStringEndsWith('basketId/' . BasketIdWhitelist::WHITELIST_IDS['PayPalPlus'], $request->getRedirectUrls()->getReturnUrl());
    }

    public function test_estimated_delivery_date_attribute_exists_but_not_set()
    {
        $this->createEddAttribute();

        $request = $this->getRequestData();

        static::assertNull($request->getTransactions()->getShipmentDetails());

        $this->deleteEddAttribute();
    }

    public function test_estimated_delivery_date_is_correct()
    {
        $this->createEddAttribute();
        $eddDays = 21;
        $date = new \DateTime();
        $date->add(new \DateInterval('P' . $eddDays . 'D'));
        $expectedDate = $date->format('Y-m-d');

        $request = $this->getRequestData($eddDays);

        static::assertSame($expectedDate, $request->getTransactions()->getShipmentDetails()->getEstimatedDeliveryDate());

        $this->deleteEddAttribute();
    }

    /**
     * @param int|null $edd
     *
     * @return Payment
     */
    private function getRequestData($edd = null)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false, 0);

        $plusPaymentBuilder = $this->getPlusPaymentBuilder($settingService);

        $basketData = $this->getBasketDataArray($edd);
        $userData = $this->getUserDataAsArray();

        $params = new PaymentBuilderParameters();
        $params->setBasketData($basketData);
        $params->setUserData($userData);

        return $plusPaymentBuilder->getPayment($params);
    }

    /**
     * @return PlusPaymentBuilderService
     */
    private function getPlusPaymentBuilder(SettingsServiceInterface $settingService)
    {
        $router = Shopware()->Container()->get('router');
        $crudService = Shopware()->Container()->get('shopware_attribute.crud_service');
        $snippetManager = Shopware()->Container()->get('snippets');
        $dependencyProvider = Shopware()->Container()->get('paypal_unified.dependency_provider');

        return new PlusPaymentBuilderService($router, $settingService, $crudService, $snippetManager, $dependencyProvider);
    }

    /**
     * @param int|null $edd
     *
     * @return array
     */
    private function getBasketDataArray($edd = null)
    {
        $basket = [
            'Amount' => '59,99',
            'AmountNet' => '50,41',
            'Quantity' => 1,
            'AmountNumeric' => 114.99000000000001,
            'AmountNetNumeric' => 96.629999999999995,
            'AmountWithTax' => '136,8381',
            'AmountWithTaxNumeric' => 136.8381,
            'sShippingcostsWithTax' => 55.0,
            'sShippingcostsNet' => 46.219999999999999,
            'sAmountTax' => 18.359999999999999,
            'sAmountWithTax' => 136.8381,
            'content' => [
                [
                    'ordernumber' => 'SW10137',
                    'articlename' => 'Fahrerbrille Chronos',
                    'quantity' => '1',
                    'price' => '59,99',
                    'netprice' => '50.411764705882',
                ],
            ],
        ];

        if ($edd !== null) {
            $basket['content'][0]['additional_details'][PlusPaymentBuilderService::EDD_ATTRIBUTE_COLUMN_NAME] = $edd;
        }

        return $basket;
    }

    /**
     * @return array
     */
    private function getUserDataAsArray()
    {
        return [
            'additional' => [
                'show_net' => true,
                'countryShipping' => [
                    'taxfree' => '0',
                ],
            ],
        ];
    }

    private function createEddAttribute()
    {
        $attributeService = Shopware()->Container()->get('shopware_attribute.crud_service');

        $attributeService->update(
            's_articles_attributes',
            PlusPaymentBuilderService::EDD_ATTRIBUTE_COLUMN_NAME,
            'integer'
        );
    }

    private function deleteEddAttribute()
    {
        $attributeService = Shopware()->Container()->get('shopware_attribute.crud_service');

        $attributeService->delete(
            's_articles_attributes',
            PlusPaymentBuilderService::EDD_ATTRIBUTE_COLUMN_NAME
        );
    }
}
