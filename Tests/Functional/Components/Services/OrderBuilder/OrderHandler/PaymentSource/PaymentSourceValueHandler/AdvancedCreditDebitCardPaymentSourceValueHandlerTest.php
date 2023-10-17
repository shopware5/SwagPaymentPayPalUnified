<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\OrderBuilder\OrderHandler\PaymentSource\PaymentSourceValueHandler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler\AdvancedCreditDebitCardPaymentSourceValueHandler;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\Card;
use SwagPaymentPayPalUnified\Tests\Functional\AssertStringContainsTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class AdvancedCreditDebitCardPaymentSourceValueHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;
    use AssertStringContainsTrait;

    /**
     * @return void
     */
    public function testSupports()
    {
        static::assertTrue($this->createAdvancedCreditDebitCardPaymentSourceValueHandler()->supports(PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD));
        static::assertFalse($this->createAdvancedCreditDebitCardPaymentSourceValueHandler()->supports('ANY_OTHER'));
    }

    /**
     * @return void
     */
    public function testCreatePaymentSourceValue()
    {
        $payPalPaymentSourceValueHandler = $this->createAdvancedCreditDebitCardPaymentSourceValueHandler();

        $result = $payPalPaymentSourceValueHandler->createPaymentSourceValue($this->createPayPalOrderParameter());

        static::assertInstanceOf(Card::class, $result);

        static::assertNull($result->getExperienceContext());
    }

    /**
     * @return AdvancedCreditDebitCardPaymentSourceValueHandler
     */
    private function createAdvancedCreditDebitCardPaymentSourceValueHandler()
    {
        return new AdvancedCreditDebitCardPaymentSourceValueHandler(
            $this->createSettingsServiceMock(),
            $this->createContextServiceMock(),
            $this->getContainer()->get('paypal_unified.common.return_url_helper')
        );
    }

    /**
     * @return SettingsServiceInterface&MockObject
     */
    private function createSettingsServiceMock()
    {
        $generalSettingsMock = new General();
        $generalSettingsMock->setShopId('1');
        $generalSettingsMock->setBrandName('anyBrandName');
        $generalSettingsMock->setLandingPageType('NO_PREFERENCE');

        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->method('getSettings')->willReturn($generalSettingsMock);

        return $settingsServiceMock;
    }

    /**
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter()
    {
        return new PayPalOrderParameter(
            require __DIR__ . '/../../_fixtures/b2c_customer.php',
            require __DIR__ . '/../../_fixtures/b2c_basket.php',
            PaymentType::PAYPAL_ADVANCED_CREDIT_DEBIT_CARD,
            'AnyBasketId',
            null,
            ''
        );
    }

    /**
     * @return ContextServiceInterface&MockObject
     */
    private function createContextServiceMock()
    {
        $shopContext = $this->getContainer()->get('shopware_storefront.context_service')->createShopContext(1);
        $contextServiceMock = $this->createMock(ContextServiceInterface::class);
        $contextServiceMock->method('getShopContext')->willReturn($shopContext);

        return $contextServiceMock;
    }
}
