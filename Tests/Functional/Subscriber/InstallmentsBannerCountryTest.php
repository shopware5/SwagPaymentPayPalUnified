<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Locale;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\InstallmentsBanner;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;

class InstallmentsBannerCountryTest extends TestCase
{
    /**
     * @dataProvider onPostDispatchSecureWithGivenCountriesTestDataProvider
     *
     * @param string $locale
     * @param string $currency
     * @param bool   $expectResult
     *
     * @return void
     */
    public function testOnPostDispatchSecureWithGivenCountries($locale, $currency, $expectResult)
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $view = new Enlight_View_Default(new Enlight_Template_Manager());

        $eventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController(
                $request,
                $view,
                new Enlight_Controller_Response_ResponseTestCase()
            ),
        ]);

        $installmentsBannerSubscriber = $this->createInstallmentsBanner($locale, $currency);

        $installmentsBannerSubscriber->onPostDispatchSecure($eventArgs);

        if (!$expectResult) {
            static::assertNull($view->getAssign('paypalUnifiedInstallmentsBanner'));
            static::assertNull($view->getAssign('paypalUnifiedInstallmentsBannerClientId'), 'paypalUnifiedInstallmentsBannerClientId is NULL');
            static::assertNull($view->getAssign('paypalUnifiedInstallmentsBannerAmount'), 'paypalUnifiedInstallmentsBannerAmount is NULL');
            static::assertNull($view->getAssign('paypalUnifiedInstallmentsBannerCurrency'), 'paypalUnifiedInstallmentsBannerCurrency is NULL');
            static::assertNull($view->getAssign('paypalUnifiedInstallmentsBannerBuyerCountry'), 'paypalUnifiedInstallmentsBannerBuyerCountry is NULL');

            return;
        }

        static::assertTrue($view->getAssign('paypalUnifiedInstallmentsBanner'));
        static::assertNotNull($view->getAssign('paypalUnifiedInstallmentsBannerClientId'), 'paypalUnifiedInstallmentsBannerClientId is NULL');
        static::assertNotNull($view->getAssign('paypalUnifiedInstallmentsBannerAmount'), 'paypalUnifiedInstallmentsBannerAmount is NULL');
        static::assertNotNull($view->getAssign('paypalUnifiedInstallmentsBannerCurrency'), 'paypalUnifiedInstallmentsBannerCurrency is NULL');
        static::assertNotNull($view->getAssign('paypalUnifiedInstallmentsBannerBuyerCountry'), 'paypalUnifiedInstallmentsBannerBuyerCountry is NULL');
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function onPostDispatchSecureWithGivenCountriesTestDataProvider()
    {
        yield 'ISO is de_DE' => [
            'de_DE',
            'EUR',
            true,
        ];

        yield 'ISO is en_AU' => [
            'en_AU',
            'AUD',
            true,
        ];

        yield 'ISO is en_GB' => [
            'en_GB',
            'GBP',
            true,
        ];

        yield 'ISO is en_US' => [
            'en_US',
            'USD',
            true,
        ];

        yield 'ISO is fr_FR' => [
            'fr_FR',
            'EUR',
            true,
        ];

        yield 'ISO is es_ES' => [
            'es_ES',
            'EUR',
            true,
        ];

        yield 'ISO is it_IT' => [
            'it_IT',
            'EUR',
            true,
        ];

        yield 'ISO is ao_AO (any other)' => [
            'ao_AO',
            'EUR',
            false,
        ];
    }

    /**
     * @return SettingsServiceInterface&MockObject
     */
    public function createSettingsServiceMock()
    {
        $settingsServiceMock = $this->createMock(SettingsServiceInterface::class);
        $settingsServiceMock->expects(static::once())->method('hasSettings')->willReturn(true);
        $settingsServiceMock->method('get')->willReturnMap([
            [SettingsServiceInterface::SETTING_GENERAL_ACTIVE, SettingsTable::GENERAL, true],
            [SettingsServiceInterface::SETTING_GENERAL_CLIENT_ID, SettingsTable::GENERAL, 'clientId'],
            [SettingsServiceInterface::SETTING_GENERAL_ADVERTISE_INSTALLMENTS, SettingsTable::INSTALLMENTS, true],
        ]);

        static::assertInstanceOf(SettingsServiceInterface::class, $settingsServiceMock);

        return $settingsServiceMock;
    }

    /**
     * @param string $locale
     * @param string $currency
     *
     * @return InstallmentsBanner
     */
    private function createInstallmentsBanner($locale, $currency)
    {
        return new InstallmentsBanner(
            $this->createSettingsServiceMock(),
            $this->createContextServiceMock($locale, $currency),
            $this->createPaymentMethodProviderMock()
        );
    }

    /**
     * @param string $locale
     * @param string $currency
     *
     * @return ContextServiceInterface&MockObject
     */
    private function createContextServiceMock($locale, $currency)
    {
        $localeMock = $this->createMock(Locale::class);
        $localeMock->method('getLocale')->willReturn($locale);

        $shopMock = $this->createMock(Shop::class);
        $shopMock->method('getLocale')->willReturn($localeMock);

        $currencyMock = $this->createMock(Currency::class);
        $currencyMock->method('getCurrency')->willReturn($currency);

        $shopContextMock = $this->createMock(ShopContext::class);
        $shopContextMock->method('getShop')->willReturn($shopMock);
        $shopContextMock->method('getCurrency')->willReturn($currencyMock);

        $contextServiceMock = $this->createMock(ContextServiceInterface::class);
        $contextServiceMock->method('getShopContext')->willReturn($shopContextMock);

        static::assertInstanceOf(ContextServiceInterface::class, $contextServiceMock);

        return $contextServiceMock;
    }

    /**
     * @return PaymentMethodProviderInterface&MockObject
     */
    private function createPaymentMethodProviderMock()
    {
        $paymentMethodProviderMock = $this->createMock(PaymentMethodProviderInterface::class);
        $paymentMethodProviderMock->method('getPaymentMethodActiveFlag')->willReturn(true);

        static::assertInstanceOf(PaymentMethodProviderInterface::class, $paymentMethodProviderMock);

        return $paymentMethodProviderMock;
    }
}
