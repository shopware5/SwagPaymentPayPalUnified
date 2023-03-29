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
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\ApplicationContext;
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
     * @return PayPalOrderParameter
     */
    private function createPayPalOrderParameter()
    {
        return new PayPalOrderParameter(
            [],
            [],
            PaymentType::PAYPAL_CLASSIC_V2,
            'basketUniqueId',
            null,
            'anyOrderId'
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
            $this->getContainer()->get('paypal_unified.common.price_formatter'),
            $this->getContainer()->get('paypal_unified.common.customer_helper'),
            $this->getContainer()->get('snippets')
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
