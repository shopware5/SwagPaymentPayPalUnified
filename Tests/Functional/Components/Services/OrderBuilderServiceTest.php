<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services;

use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Discount;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\ItemTotal;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Amount\Breakdown\Shipping;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class OrderBuilderServiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use SettingsHelperTrait;
    use ContainerTrait;

    /**
     * @dataProvider getOrderTestDataProvider
     *
     * @param string $intent
     * @param bool   $expectException
     * @param string $exceptionMessage
     */
    public function testGetOrder($intent, $expectException, $exceptionMessage)
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/order_builder_service_test.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'active' => true,
            'landingPageType' => 'NO_PREFERENCE',
            'submitCart' => 1,
            'intent' => 'CAPTURE',
            'brandName' => 'DefaultTestBrandName',
        ]);

        $this->getContainer()->get('session')->offsetSet('sUserId', 3);

        $sql = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->update('swag_payment_paypal_unified_settings_general')
            ->set('intent', ':intent')
            ->setParameter('intent', $intent)
            ->execute();

        $userData = require __DIR__ . '/_fixtures/OrderBuilderServiceTestUserData.php';
        $basketData = require __DIR__ . '/_fixtures/OrderBuilderServiceTestBasketData.php';

        $shopwareOrderData = new ShopwareOrderData($userData, $basketData);
        $orderParams = $this->getContainer()
            ->get('paypal_unified.paypal_order_parameter_facade')
            ->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2, $shopwareOrderData);

        if ($expectException) {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $payPalOrderData = $this->getContainer()
            ->get('paypal_unified.order_factory')
            ->createOrder($orderParams);

        // Check intent
        static::assertSame($intent, $payPalOrderData->getIntent());

        // Check Payer data
        static::assertSame('PhpUnit', $payPalOrderData->getPayer()->getName()->getGivenName());
        static::assertSame('Tester', $payPalOrderData->getPayer()->getName()->getSurname());
        static::assertSame('phpUnit.tester@test.com', $payPalOrderData->getPayer()->getEmailAddress());
        static::assertSame('FooBarStreet, 42', $payPalOrderData->getPayer()->getAddress()->getAddressLine1());
        static::assertNull($payPalOrderData->getPayer()->getAddress()->getAddressLine2());
        static::assertNull($payPalOrderData->getPayer()->getAddress()->getAdminArea1());
        static::assertSame('SinCity', $payPalOrderData->getPayer()->getAddress()->getAdminArea2());
        static::assertSame('12345', $payPalOrderData->getPayer()->getAddress()->getPostalCode());
        static::assertSame('DE', $payPalOrderData->getPayer()->getAddress()->getCountryCode());

        // Check application context data
        static::assertSame('DefaultTestBrandName', $payPalOrderData->getApplicationContext()->getBrandName());
        static::assertSame('NO_PREFERENCE', $payPalOrderData->getApplicationContext()->getLandingPage());
        static::assertSame('SET_PROVIDED_ADDRESS', $payPalOrderData->getApplicationContext()->getShippingPreference());
        static::assertSame('PAY_NOW', $payPalOrderData->getApplicationContext()->getUserAction());

        if (method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('/PaypalUnifiedV2/return', $payPalOrderData->getApplicationContext()->getReturnUrl());
            static::assertStringContainsString('/PaypalUnifiedV2/cancel', $payPalOrderData->getApplicationContext()->getCancelUrl());
        } else {
            static::assertContains('/PaypalUnifiedV2/return', $payPalOrderData->getApplicationContext()->getReturnUrl());
            static::assertContains('/PaypalUnifiedV2/cancel', $payPalOrderData->getApplicationContext()->getCancelUrl());
        }

        // Check purchase units
        static::assertTrue(\is_array($payPalOrderData->getPurchaseUnits()));
        static::assertCount(1, $payPalOrderData->getPurchaseUnits());

        $amount = $payPalOrderData->getPurchaseUnits()[0]->getAmount();
        static::assertInstanceOf(Amount::class, $amount);

        static::assertSame('EUR', $amount->getCurrencyCode());
        static::assertSame('543.96', $amount->getValue());

        // Check purchase units breakdown
        static::assertInstanceOf(Breakdown::class, $amount->getBreakdown());

        static::assertInstanceOf(ItemTotal::class, $amount->getBreakdown()->getItemTotal());
        static::assertSame('EUR', $amount->getBreakdown()->getItemTotal()->getCurrencyCode());
        static::assertSame('468.96', $amount->getBreakdown()->getItemTotal()->getValue());

        static::assertInstanceOf(Shipping::class, $amount->getBreakdown()->getShipping());
        static::assertSame('EUR', $amount->getBreakdown()->getShipping()->getCurrencyCode());
        static::assertSame('75.00', $amount->getBreakdown()->getShipping()->getValue());

        if ($amount->getBreakdown()->getDiscount() !== null) {
            static::assertInstanceOf(Discount::class, $amount->getBreakdown()->getDiscount());
            static::assertSame('EUR', $amount->getBreakdown()->getDiscount()->getCurrencyCode());
            static::assertSame('0.00', $amount->getBreakdown()->getDiscount()->getValue());
        }

        // Check purchase units shipping
        static::assertSame('PhpUnit Tester', $payPalOrderData->getPurchaseUnits()[0]->getShipping()->getName()->getFullName());
        static::assertSame('FooBarStreet, 42', $payPalOrderData->getPurchaseUnits()[0]->getShipping()->getAddress()->getAddressLine1());
        static::assertNull($payPalOrderData->getPurchaseUnits()[0]->getShipping()->getAddress()->getAddressLine2());
        static::assertNull($payPalOrderData->getPurchaseUnits()[0]->getShipping()->getAddress()->getAdminArea1());
        static::assertSame('SinCity', $payPalOrderData->getPurchaseUnits()[0]->getShipping()->getAddress()->getAdminArea2());
        static::assertSame('12345', $payPalOrderData->getPurchaseUnits()[0]->getShipping()->getAddress()->getPostalCode());
        static::assertSame('DE', $payPalOrderData->getPurchaseUnits()[0]->getShipping()->getAddress()->getCountryCode());

        // Check purchase units items
        static::assertTrue(\is_array($payPalOrderData->getPurchaseUnits()[0]->getItems()));
        static::assertCount(10, $payPalOrderData->getPurchaseUnits()[0]->getItems());

        foreach ($payPalOrderData->getPurchaseUnits()[0]->getItems() as $item) {
            static::assertSame('PHYSICAL_GOODS', $item->getCategory());
        }
    }

    /**
     * @return Generator<array<mixed>>
     */
    public function getOrderTestDataProvider()
    {
        yield 'Intent should be authorize' => [
            'AUTHORIZE',
            false,
            '',
        ];

        yield 'Intent should be capture' => [
            'CAPTURE',
            false,
            '',
        ];

        yield 'Creating $payPalOrderData should throw a runtime exception' => [
            'ANY_OTHER_INTENT',
            true,
            'The intent ANY_OTHER_INTENT is not supported!',
        ];

        yield 'Creating $payPalOrderData should throw a runtime exception with empty intent in message' => [
            '',
            true,
            'The intent  is not supported!',
        ];
    }
}
