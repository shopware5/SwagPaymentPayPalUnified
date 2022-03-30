<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\Subscriber\PaymentMeans;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Mock\PaymentMeansSubscriberTest\EventArgsMockWithoutReturn;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Mock\PaymentMeansSubscriberTest\EventArgsMockWithoutUnifiedReturn;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Mock\PaymentMeansSubscriberTest\EventArgsMockWithUnifiedReturn;

class PaymentMeansSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;
    use SettingsHelperTrait;

    /**
     * @return void
     */
    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertSame(PaymentMeans::class, \get_class($subscriber));
    }

    /**
     * @return void
     */
    public function testGetSubscribedEvents()
    {
        $events = PaymentMeans::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('onFilterPaymentMeans', $events['Shopware_Modules_Admin_GetPaymentMeans_DataFilter']);
    }

    /**
     * @return void
     */
    public function testOnFilterPaymentMeansWithoutAvailableMethods()
    {
        $subscriber = $this->getSubscriber();

        $args = new EventArgsMockWithoutReturn();
        $subscriber->onFilterPaymentMeans($args);

        static::assertCount(0, $args->result);
    }

    /**
     * @return void
     */
    public function testOnFilterPaymentMeansWithoutUnifiedMethod()
    {
        $subscriber = $this->getSubscriber();

        $args = new EventArgsMockWithoutUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);

        static::assertCount(5, $args->result);
    }

    /**
     * @return void
     */
    public function testOnFilterPaymentMeansHasUnifiedMethod()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createTestSettings();

        $args = new EventArgsMockWithUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        static::assertCount(6, $result);
        static::assertSame($this->getUnifiedPaymentId(), $result[5]['id']);
    }

    /**
     * @return void
     */
    public function testOnFilterPaymentMeansHasNoUnifiedMethodBecauseTheSettingsDontExist()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createTestSettings(false);

        $args = new EventArgsMockWithUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        static::assertCount(5, $result);
        static::assertNotContains($this->getUnifiedPaymentId(), $result);
    }

    /**
     * @param bool $mockSettings
     *
     * @return PaymentMeans
     */
    private function getSubscriber($mockSettings = true)
    {
        if ($mockSettings) {
            $settingServiceMock = $this->createMock(SettingsServiceInterface::class);
            $settingServiceMock->method('hasSettings')->willReturn(false);
            $settingServiceMock->method('getSettings')->willReturn(null);

            return new PaymentMeans(
                $settingServiceMock,
                Shopware()->Container()->get('paypal_unified.payment_method_provider')
            );
        }

        return new PaymentMeans(
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.payment_method_provider')
        );
    }

    /**
     * @param bool $active
     *
     * @return void
     */
    private function createTestSettings($active = true)
    {
        $this->insertGeneralSettingsFromArray([
            'shopId' => 1,
            'clientId' => 'test',
            'clientSecret' => 'test',
            'sandbox' => true,
            'showSidebarLogo' => true,
            'active' => $active,
        ]);

        $this->insertPlusSettingsFromArray([
            'active' => 1,
            'shopId' => 1,
        ]);
    }
}
