<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Components\Services\Mock;

use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class SettingsServicePaymentBuilderServiceMock implements SettingsServiceInterface
{
    /**
     * @var bool
     */
    private $plus_active;

    /**
     * @var int
     */
    private $paypal_payment_intent;

    /**
     * @var bool
     */
    private $ec_submit_cart;

    /**
     * @var bool
     */
    private $submitCartGeneral;

    /**
     * @var bool
     */
    private $longBrandName;

    /**
     * @param bool $plusActive
     * @param int  $paypalPaymentIntent
     * @param bool $submitCartEcs
     * @param bool $submitCartGeneral
     * @param bool$longBrandName
     */
    public function __construct(
        $plusActive,
        $paypalPaymentIntent,
        $submitCartEcs = true,
        $submitCartGeneral = true,
        $longBrandName = false
    ) {
        // do not delete, even if PHPStorm says they are unused
        // used in the get() method
        $this->plus_active = $plusActive;
        $this->paypal_payment_intent = $paypalPaymentIntent;
        $this->ec_submit_cart = $submitCartEcs;
        $this->submitCartGeneral = $submitCartGeneral;
        $this->longBrandName = $longBrandName;
    }

    public function getSettings($shopId = null, $settingsTable = SettingsTable::GENERAL)
    {
    }

    public function get($column, $settingsTable = SettingsTable::GENERAL)
    {
        if ($column === 'active' && $settingsTable === SettingsTable::PLUS) {
            return $this->plus_active;
        }

        if ($column === 'intent') {
            return $this->paypal_payment_intent;
        }

        if ($column === 'submit_cart' && $settingsTable === SettingsTable::EXPRESS_CHECKOUT) {
            return $this->ec_submit_cart;
        }

        if ($column === 'submit_cart' && $settingsTable === SettingsTable::GENERAL) {
            return $this->submitCartGeneral;
        }

        if ($column === 'brand_name') {
            if ($this->longBrandName === false) {
                return 'TestBrandName';
            }

            return 'Lorem ipsum dolor sit amet consetetur sadipscing elitr sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam';
        }

        return $this->$column;
    }

    public function hasSettings($settingsTable = SettingsTable::GENERAL)
    {
    }

    public function refreshDependencies()
    {
    }
}
