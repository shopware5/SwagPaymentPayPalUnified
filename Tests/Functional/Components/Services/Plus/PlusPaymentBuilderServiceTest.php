<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Plus;

use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PaymentBuilderParameters;
use SwagPaymentPayPalUnified\Components\Services\Plus\PlusPaymentBuilderService;
use SwagPaymentPayPalUnified\Components\Services\Validation\BasketIdWhitelist;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ShipmentDetails;
use SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock\SettingsServicePaymentBuilderServiceMock;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PlusPaymentBuilderServiceTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    public function testServiceIsAvailable()
    {
        $service = $this->getContainer()->get('paypal_unified.plus.payment_builder_service');
        static::assertSame(PlusPaymentBuilderService::class, \get_class($service));
    }

    public function testGetPaymentHasPlusBasketId()
    {
        $request = $this->getRequestData();

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString(
                '/PaypalUnified/return/plus/1/basketId/' . BasketIdWhitelist::WHITELIST_IDS['PayPalPlus'],
                $request->getRedirectUrls()->getReturnUrl()
            );

            return;
        }
        static::assertContains(
            '/PaypalUnified/return/plus/1/basketId/' . BasketIdWhitelist::WHITELIST_IDS['PayPalPlus'],
            $request->getRedirectUrls()->getReturnUrl()
        );
    }

    public function testEstimatedDeliveryDateAttributeExistsButNotSet()
    {
        $this->createEddAttribute();

        $request = $this->getRequestData();

        static::assertNull($request->getTransactions()->getShipmentDetails());

        $this->deleteEddAttribute();
    }

    public function testEstimatedDeliveryDateIsCorrect()
    {
        $this->createEddAttribute();
        $eddDays = 21;
        $date = new DateTime();
        $date->add(new DateInterval('P' . $eddDays . 'D'));
        $expectedDate = $date->format('Y-m-d');

        $request = $this->getRequestData($eddDays);

        $shipmentDetails = $request->getTransactions()->getShipmentDetails();
        static::assertInstanceOf(ShipmentDetails::class, $shipmentDetails);
        static::assertSame($expectedDate, $shipmentDetails->getEstimatedDeliveryDate());

        $this->deleteEddAttribute();
    }

    /**
     * @param int|null $edd
     *
     * @return Payment
     */
    private function getRequestData($edd = null)
    {
        $settingService = new SettingsServicePaymentBuilderServiceMock(false);

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
        return $this->getContainer()->get('paypal_unified.plus.payment_builder_service');
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
        $attributeService = $this->getContainer()->get('shopware_attribute.crud_service');

        $attributeService->update(
            's_articles_attributes',
            PlusPaymentBuilderService::EDD_ATTRIBUTE_COLUMN_NAME,
            'integer'
        );
    }

    private function deleteEddAttribute()
    {
        $attributeService = $this->getContainer()->get('shopware_attribute.crud_service');

        $attributeService->delete(
            's_articles_attributes',
            PlusPaymentBuilderService::EDD_ATTRIBUTE_COLUMN_NAME
        );
    }
}
