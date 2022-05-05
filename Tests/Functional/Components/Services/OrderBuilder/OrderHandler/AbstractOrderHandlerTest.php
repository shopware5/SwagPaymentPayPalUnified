<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler;

use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler\AbstractOrderHandler;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\Tax;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Item\UnitAmount;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class AbstractOrderHandlerTest extends TestCase
{
    use ContainerTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    const CURRENCY_CODE = 'EUR';

    const TAX_RATE = '19.0';

    /**
     * @dataProvider landingPageDataProvider
     *
     * @param string $loginType
     *
     * @return void
     */
    public function testCreateApplicationContextShouldAddLoginAsLadingPage($loginType)
    {
        $this->insertGeneralSettingsFromArray(['shopId' => 1, 'landingPageType' => 'identifier']);

        $this->updateSettings($loginType);
        $oderParameter = $this->createPayPalOrderParameter();

        $abstractOrderHandler = $this->createOrderHandlerMock();

        $result = $abstractOrderHandler->createApplicationContextWrapper($oderParameter);

        static::assertSame($loginType, $result->getLandingPage());
    }

    /**
     * @return Generator<array<ApplicationContext::*>>
     */
    public function landingPageDataProvider()
    {
        yield 'LandingPage should be LOGIN' => [
            ApplicationContext::LANDING_PAGE_TYPE_LOGIN,
        ];

        yield 'LandingPage should be BILLING' => [
            ApplicationContext::LANDING_PAGE_TYPE_BILLING,
        ];

        yield 'LandingPage should be NO_PREFERENCE' => [
            ApplicationContext::LANDING_PAGE_TYPE_NO_PREFERENCE,
        ];
    }

    /**
     * @return void
     */
    public function testAddVirtualHandlingAndDiscounts()
    {
        $purchaseUnit = new PurchaseUnit();

        $amount = new PurchaseUnit\Amount();
        $amount->setCurrencyCode(self::CURRENCY_CODE);

        $breakdown = new PurchaseUnit\Amount\Breakdown();

        $itemTotal = new PurchaseUnit\Amount\Breakdown\ItemTotal();
        $itemTotal->setCurrencyCode(self::CURRENCY_CODE);
        $itemTotal->setValue('100.00');

        $taxTotal = new PurchaseUnit\Amount\Breakdown\TaxTotal();
        $taxTotal->setCurrencyCode(self::CURRENCY_CODE);
        $taxTotal->setValue('19.00');

        $breakdown->setItemTotal($itemTotal);
        $breakdown->setTaxTotal($taxTotal);

        $amount->setBreakdown($breakdown);

        $item = new Item();
        $item->setName('Some Product');
        $item->setTaxRate(self::TAX_RATE);
        $item->setQuantity(1);
        $item->setSku('SW12345');
        $item->setCategory('PHYSICAL_GOODS');

        $itemUnitAmount = new UnitAmount();
        $itemUnitAmount->setValue('100.00');
        $itemUnitAmount->setCurrencyCode(self::CURRENCY_CODE);

        $item->setUnitAmount($itemUnitAmount);
        $itemTax = new Tax();
        $itemTax->setCurrencyCode(self::CURRENCY_CODE);
        $itemTax->setValue('19.00');

        $item->setTax($itemTax);

        $taxItem = new Item();
        $taxItem->setName('DISCOUNT');
        $taxItem->setTaxRate(self::TAX_RATE);
        $taxItem->setQuantity(1);
        $taxItem->setSku('DISCOUNT');
        $taxItem->setCategory('PHYSICAL_GOODS');

        $taxItemUnitAmount = new UnitAmount();
        $taxItemUnitAmount->setValue('-20.00');
        $taxItemUnitAmount->setCurrencyCode(self::CURRENCY_CODE);
        $taxItem->setUnitAmount($taxItemUnitAmount);

        $purchaseUnit->setItems([$item, $taxItem]);
        $purchaseUnit->setAmount($amount);

        $abstractOrderHandler = $this->createOrderHandlerMock();

        $reflectionMethod = (new ReflectionClass(AbstractOrderHandler::class))->getMethod('addVirtualHandlingAndDiscounts');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invoke($abstractOrderHandler, $purchaseUnit);

        $amountResult = $purchaseUnit->getAmount();
        static::assertInstanceOf(PurchaseUnit\Amount::class, $amountResult);

        $breakdownResult = $amountResult->getBreakdown();
        static::assertInstanceOf(PurchaseUnit\Amount\Breakdown::class, $breakdownResult);

        $discountResult = $breakdownResult->getDiscount();
        static::assertInstanceOf(PurchaseUnit\Amount\Breakdown\Discount::class, $discountResult);
        static::assertSame('20.00', $discountResult->getValue());
    }

    /**
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter()
    {
        return new PayPalOrderParameter(
            [],
            [],
            PaymentType::PAYPAL_CLASSIC_V2,
            'basketUniqueId',
            null
        );
    }

    /**
     * @return OrderHandlerMock
     */
    private function createOrderHandlerMock()
    {
        return new OrderHandlerMock(
            $this->getContainer()->get('paypal_unified.settings_service'),
            $this->getContainer()->get('paypal_unified.paypal_order.item_list_provider'),
            $this->getContainer()->get('paypal_unified.paypal_order.amount_provider'),
            $this->getContainer()->get('paypal_unified.common.return_url_helper'),
            $this->getContainer()->get('shopware_storefront.context_service'),
            $this->getContainer()->get('paypal_unified.phone_number_builder'),
            $this->getContainer()->get('paypal_unified.common.price_formatter')
        );
    }

    /**
     * @param string $landingPageType
     *
     * @return void
     */
    private function updateSettings($landingPageType)
    {
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->update('swag_payment_paypal_unified_settings_general')
            ->set('landing_page_type', ':landingPageType')
            ->where('landing_page_type LIKE "identifier"')
            ->setParameter('landingPageType', $landingPageType)
            ->execute();
    }
}
