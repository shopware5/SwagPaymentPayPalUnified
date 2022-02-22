<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Components;

use Shopware\Components\Model\ModelEntity;

interface SettingsServiceInterface
{
    const SETTING_INTENT = 'intent';
    const SETTING_SUBMIT_CART = 'submit_cart';
    const SETTING_BRAND_NAME = 'brand_name';
    const SETTING_DISPLAY_ERRORS = 'display_errors';
    const SETTING_ACTIVE = 'active';
    const SETTING_SANDBOX = 'sandbox';
    const SETTING_CLIENT_ID = 'client_id';
    const SETTING_CLIENT_SECRET = 'client_secret';
    const SETTING_SANDBOX_CLIENT_ID = 'sandbox_client_id';
    const SETTING_SANDBOX_CLIENT_SECRET = 'sandbox_client_secret';
    const SETTING_SHOW_SIDEBAR_LOGO = 'show_sidebar_logo';
    const SETTING_ADVERTISE_INSTALLMENTS = 'advertise_installments';
    const SETTING_INTEGRATE_THIRD_PARTY_METHODS = 'integrate_third_party_methods';
    const SETTING_RESTYLE = 'restyle';
    const SETTING_PAYMENT_NAME = 'payment_name';
    const SETTING_PAYMENT_DESCRIPTION = 'payment_description';
    const SETTING_LANDING_PAGE_TYPE = 'landing_page_type';
    const SETTING_SEND_ORDER_NUMBER = 'send_order_number';
    const SETTING_ORDER_NUMBER_PREFIX = 'order_number_prefix';
    const SETTING_LOG_LEVEL = 'log_level';
    const SETTING_CART_ACTIVE = 'cart_active';
    const SETTING_DETAIL_ACTIVE = 'detail_active';
    const SETTING_LOGIN_ACTIVE = 'login_active';
    const SETTING_OFF_CANVAS_ACTIVE = 'off_canvas_active';

    /**
     * Returns the whole settings model
     *
     * @param int|null $shopId
     * @param string   $settingsTable
     *
     * @return ModelEntity|null
     *
     * @see SettingsTable
     */
    public function getSettings($shopId = null, $settingsTable = SettingsTable::GENERAL);

    /**
     * Returns a setting value by the provided column name.
     *
     * @param string $column
     * @param string $settingsTable
     *
     * @see SettingsTable
     */
    public function get($column, $settingsTable = SettingsTable::GENERAL);

    /**
     * Returns a boolean indicating if the shop has any stored settings for the current shop.
     *
     * @param string $settingsTable
     *
     * @return bool
     *
     * @see SettingsTable
     */
    public function hasSettings($settingsTable = SettingsTable::GENERAL);

    /**
     * A helper function that refreshes the dependencies. Most commonly used in the backend to refresh the selected shop.
     */
    public function refreshDependencies();
}
