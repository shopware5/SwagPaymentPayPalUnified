<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\Subscriber\PaymentMeans;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;

class PaymentMeansSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;
    use SettingsHelperTrait;

    public function test_can_be_created()
    {
        $subscriber = $this->getSubscriber();
        $this->assertEquals(PaymentMeans::class, get_class($subscriber));
    }

    public function test_getSubscribedEvents()
    {
        $events = PaymentMeans::getSubscribedEvents();
        $this->assertCount(1, $events);
        $this->assertEquals('onFilterPaymentMeans', $events['Shopware_Modules_Admin_GetPaymentMeans_DataFilter']);
    }

    public function test_onFilterPaymentMeans_without_available_methods()
    {
        $subscriber = $this->getSubscriber();

        $args = new EventArgsMockWithoutReturn();
        $subscriber->onFilterPaymentMeans($args);

        $this->assertCount(0, $args->result);
    }

    public function test_onFilterPaymentMeans_without_unified_method()
    {
        $subscriber = $this->getSubscriber();

        $args = new EventArgsMockWithoutUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);

        $this->assertCount(5, $args->result);
    }

    public function test_onFilterPaymentMeans_has_unified_method()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createTestSettings();

        $args = new EventArgsMockWithUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(6, $result);
        $this->assertEquals($this->getUnifiedPaymentId(), $result[5]['id']);
    }

    public function test_onFilterPaymentMeans_has_no_unified_method_because_the_settings_dont_exist()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createTestSettings(false);

        $args = new EventArgsMockWithUnifiedReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
        $this->assertNotContains($this->getUnifiedPaymentId(), $result);
    }

    public function test_onFilterPaymentMeans_has_no_installments_method_because_the_settings_dont_exist()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createTestSettings();

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
        $this->assertNotContains($this->getInstallmentsPaymentId(), $result);
    }

    public function test_onFilterPaymentMeans_installments_with_sOrderVariables_null()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
        $this->assertNotContains($this->getInstallmentsPaymentId(), $result);
    }

    public function test_onFilterPaymentMeans_installments_with_sOrderVariables_null_but_with_register()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        $registerData = [
            'billing' => [
                'country' => 2,
            ],
        ];
        Shopware()->Session()->offsetSet('sRegister', $registerData);
        Shopware()->Session()->offsetSet('sBasketAmount', 500);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(6, $result);
    }

    public function test_onFilterPaymentMeans_installments_with_sOrderVariables_null_but_with_register_with_company()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        $registerData = [
            'billing' => [
                'company' => 'shopware AG',
                'country' => 2,
            ],
        ];
        Shopware()->Session()->offsetSet('sRegister', $registerData);
        Shopware()->Session()->offsetSet('sBasketAmount', 500);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
        $this->assertNotContains($this->getInstallmentsPaymentId(), $result);
    }

    public function test_onFilterPaymentMeans_has_no_installments_because_the_price_is_less_99()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        //This is a valid user
        $userData = [
            'additional' => [
                'country' => [
                    'countryiso' => 'DE',
                ],
            ],
        ];

        $sOrderVariables = [
            'sUserData' => $userData,
        ];

        Shopware()->Session()->offsetSet('sOrderVariables', $sOrderVariables);
        Shopware()->Session()->offsetSet('sBasketAmount', 50.00);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
    }

    public function test_onFilterPaymentMeans_has_no_installments_because_the_price_is_higher_than_5000()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        //This is a valid user
        $userData = [
            'additional' => [
                'country' => [
                    'countryiso' => 'DE',
                ],
            ],
        ];

        $sOrderVariables = [
            'sUserData' => $userData,
        ];

        Shopware()->Session()->offsetSet('sOrderVariables', $sOrderVariables);
        Shopware()->Session()->offsetSet('sBasketAmount', 10000.00);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
    }

    public function test_onFilterPaymentMeans_has_no_installments_because_business_customer()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        //This is an invalid user
        $userData = [
            'billingaddress' => [
                'company' => 'TEST_COMPANY',
            ],
            'additional' => [
                'country' => [
                    'countryiso' => 'DE',
                ],
            ],
        ];

        $sOrderVariables = [
            'sUserData' => $userData,
        ];

        Shopware()->Session()->offsetSet('sOrderVariables', $sOrderVariables);
        Shopware()->Session()->offsetSet('sBasketAmount', 1000.00);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
    }

    public function test_onFilterPaymentMeans_has_no_installments_because_country_is_not_DE()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        //This is an invalid user
        $userData = [
            'additional' => [
                'country' => [
                    'countryiso' => 'GB',
                ],
            ],
        ];

        $sOrderVariables = [
            'sUserData' => $userData,
        ];

        Shopware()->Session()->offsetSet('sOrderVariables', $sOrderVariables);
        Shopware()->Session()->offsetSet('sBasketAmount', 1000.00);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(5, $result);
    }

    public function test_onFilterPaymentMeans_has_installments()
    {
        $subscriber = $this->getSubscriber(false);
        $this->createInstallmentsTestSettings();

        $userData = [
            'additional' => [
                'country' => [
                    'countryiso' => 'DE',
                ],
            ],
        ];

        $sOrderVariables = [
            'sUserData' => $userData,
        ];

        Shopware()->Session()->offsetSet('sOrderVariables', $sOrderVariables);
        Shopware()->Session()->offsetSet('sBasketAmount', 1000.00);

        $args = new EventArgsMockWithInstallmentsReturn();
        $subscriber->onFilterPaymentMeans($args);
        $result = $args->result;

        $this->assertCount(6, $result);
    }

    private function getSubscriber($mockSettings = true)
    {
        if ($mockSettings) {
            return new PaymentMeans(
                Shopware()->Container()->get('dbal_connection'),
                new SettingsServiceMock(),
                Shopware()->Container()->get('paypal_unified.installments.validation_service'),
                Shopware()->Container()->get('session')
            );
        }

        return new PaymentMeans(
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('paypal_unified.settings_service'),
            Shopware()->Container()->get('paypal_unified.installments.validation_service'),
            Shopware()->Container()->get('session')
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
            'logoImage' => 'None',
            'active' => $active,
        ]);

        $this->insertPlusSettingsFromArray([
            'active' => 1,
            'shopId' => 1,
        ]);
    }

    private function createInstallmentsTestSettings()
    {
        $this->createTestSettings();
        $this->insertInstallmentsSettingsFromArray([
            'active' => true,
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
    }

    public function getSettings($shopId = null, $settingsTable = SettingsTable::GENERAL)
    {
    }

    public function refreshDependencies()
    {
    }
}
class EventArgsMockWithoutReturn extends \Enlight_Event_EventArgs
{
    public $result;

    public function getReturn()
    {
        return [];
    }

    public function setReturn($result)
    {
        $this->result = $result;
    }
}
class EventArgsMockWithoutUnifiedReturn extends \Enlight_Event_EventArgs
{
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

    public function setReturn($result)
    {
        $this->result = $result;
    }
}
class EventArgsMockWithUnifiedReturn extends \Enlight_Event_EventArgs
{
    use PayPalUnifiedPaymentIdTrait;

    public $result;

    public function getReturn()
    {
        return [
            ['id' => 0],
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => $this->getUnifiedPaymentId()],
        ];
    }

    public function setReturn($result)
    {
        $this->result = $result;
    }
}

class EventArgsMockWithInstallmentsReturn extends \Enlight_Event_EventArgs
{
    use PayPalUnifiedPaymentIdTrait;

    public $result;

    public function getReturn()
    {
        return [
            ['id' => 0],
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => $this->getInstallmentsPaymentId()],
        ];
    }

    public function setReturn($result)
    {
        $this->result = $result;
    }
}
