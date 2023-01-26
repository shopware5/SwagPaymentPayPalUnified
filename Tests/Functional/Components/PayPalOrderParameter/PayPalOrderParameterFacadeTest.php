<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\PayPalOrderParameter;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameterFacade;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class PayPalOrderParameterFacadeTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;
    use DatabaseTestCaseTrait;

    const GERMAN_SHOP_ID = 1;
    const BRITISH_SHOP_ID = 2;
    const GERMAN_SHOP_ORDERNUMBER_PREFIX = 'GER';
    const BRITISH_SHOP_ORDERNUMBER_PREFIX = 'BRIT';

    /**
     * @return void
     */
    public function testCreatePayPalOrderParameterWithGermanShop()
    {
        $this->insertGeneralSettingsFromArray([
            'active' => 1,
            'shop_id' => self::GERMAN_SHOP_ID,
            'order_number_prefix' => self::GERMAN_SHOP_ORDERNUMBER_PREFIX,
        ]);

        $result = $this->createPayPalOrderParameterFacade()->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2, $this->createShopwareOrderData());

        static::assertInstanceOf(PayPalOrderParameter::class, $result);
        static::assertTrue(\is_string($result->getShopwareOrderNumber()));
        static::assertStringStartsWith(self::GERMAN_SHOP_ORDERNUMBER_PREFIX, $result->getShopwareOrderNumber());
    }

    /**
     * @return void
     */
    public function testCreatePayPalOrderParameterWithBritishShop()
    {
        $this->registerShop(self::BRITISH_SHOP_ID);

        $this->insertGeneralSettingsFromArray([
            'active' => 1,
            'shop_id' => self::BRITISH_SHOP_ID,
            'order_number_prefix' => self::BRITISH_SHOP_ORDERNUMBER_PREFIX,
        ]);

        $result = $this->createPayPalOrderParameterFacade()->createPayPalOrderParameter(PaymentType::PAYPAL_CLASSIC_V2, $this->createShopwareOrderData());

        static::assertInstanceOf(PayPalOrderParameter::class, $result);
        static::assertTrue(\is_string($result->getShopwareOrderNumber()));
        static::assertStringStartsWith(self::BRITISH_SHOP_ORDERNUMBER_PREFIX, $result->getShopwareOrderNumber());
    }

    /**
     * @return ShopwareOrderData
     */
    private function createShopwareOrderData()
    {
        $userData = require __DIR__ . '/_fixtures/UserData.php';
        $basketData = require __DIR__ . '/_fixtures/BasketData.php';

        return new ShopwareOrderData($userData, $basketData);
    }

    /**
     * @return PayPalOrderParameterFacade
     */
    private function createPayPalOrderParameterFacade()
    {
        return new PayPalOrderParameterFacade(
            $this->getContainer()->get('paypal_unified.payment_controller_helper'),
            $this->getContainer()->get('paypal_unified.dependency_provider'),
            $this->getContainer()->get('paypal_unified.common.cart_persister'),
            $this->getContainer()->get('paypal_unified.order_number_service'),
            $this->getContainer()->get('paypal_unified.settings_service')
        );
    }
}
