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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\PaymentMeans;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class PaymentMeansSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;
    use SettingsHelperTrait;

    public function testCanBeCreated()
    {
        $subscriber = $this->getSubscriber();
        static::assertSame(PaymentMeans::class, \get_class($subscriber));
    }

    public function testGetSubscribedEvents()
    {
        $events = PaymentMeans::getSubscribedEvents();
        static::assertCount(1, $events);
        static::assertSame('onFilterPaymentMeans', $events['Shopware_Modules_Admin_GetPaymentMeans_DataFilter']);
    }

    public function testOnFilterPaymentMeansWithoutAvailableMethods()
    {
        $subscriber = $this->getSubscriber();

        $args = new EventArgsMockWithoutReturn();
        $subscriber->onFilterPaymentMeans($args);

        static::assertCount(0, $args->result);
    }

    public function testOnFilterPaymentMeansWithoutUnifiedMethod()
    {
        $subscriber = $this->getSubscriber();

        $args = new EventArgsMockWithoutUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);

        static::assertCount(5, $args->result);
    }

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
            return new PaymentMeans(
                new SettingsServiceMock(),
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

class SettingsServiceMock implements SettingsServiceInterface
{
    public function get($column, $settingsTable = SettingsTable::GENERAL)
    {
    }

    public function hasSettings($settingsTable = SettingsTable::GENERAL)
    {
        return false;
    }

    public function getSettings($shopId = null, $settingsTable = SettingsTable::GENERAL)
    {
        return null;
    }

    public function refreshDependencies()
    {
    }
}
class EventArgsMockWithoutReturn extends \Enlight_Event_EventArgs
{
    /**
     * @var array
     */
    public $result;

    public function getReturn()
    {
        return [];
    }

    /**
     * @param array $result
     */
    public function setReturn($result)
    {
        $this->result = $result;
    }
}
class EventArgsMockWithoutUnifiedReturn extends \Enlight_Event_EventArgs
{
    /**
     * @var array
     */
    public $result;

    public function getReturn()
    {
        return [
            ['id' => 0],
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
        ];
    }

    /**
     * @param array $result
     */
    public function setReturn($result)
    {
        $this->result = $result;
    }
}
class EventArgsMockWithUnifiedReturn extends \Enlight_Event_EventArgs
{
    use PayPalUnifiedPaymentIdTrait;

    /**
     * @var array
     */
    public $result;

    public function getReturn()
    {
        $id = $this->getUnifiedPaymentId();

        return [
            ['id' => 0],
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => $id],
        ];
    }

    /**
     * @param array $result
     */
    public function setReturn($result)
    {
        $this->result = $result;
    }
}
