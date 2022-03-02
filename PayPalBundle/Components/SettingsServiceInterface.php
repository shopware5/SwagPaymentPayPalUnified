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
    const SETTING_GENERAL_INTENT = 'intent';
    const SETTING_GENERAL_SUBMIT_CART = 'submit_cart';
    const SETTING_GENERAL_BRAND_NAME = 'brand_name';
    const SETTING_GENERAL_DISPLAY_ERRORS = 'display_errors';
    const SETTING_GENERAL_ACTIVE = 'active';
    const SETTING_GENERAL_SANDBOX = 'sandbox';
    const SETTING_GENERAL_CLIENT_ID = 'client_id';
    const SETTING_GENERAL_CLIENT_SECRET = 'client_secret';
    const SETTING_GENERAL_SANDBOX_CLIENT_ID = 'sandbox_client_id';
    const SETTING_GENERAL_SANDBOX_CLIENT_SECRET = 'sandbox_client_secret';
    const SETTING_GENERAL_SHOW_SIDEBAR_LOGO = 'show_sidebar_logo';
    const SETTING_GENERAL_ADVERTISE_INSTALLMENTS = 'advertise_installments';
    const SETTING_GENERAL_INTEGRATE_THIRD_PARTY_METHODS = 'integrate_third_party_methods';
    const SETTING_GENERAL_RESTYLE = 'restyle';
    const SETTING_GENERAL_PAYMENT_NAME = 'payment_name';
    const SETTING_GENERAL_PAYMENT_DESCRIPTION = 'payment_description';
    const SETTING_GENERAL_LANDING_PAGE_TYPE = 'landing_page_type';
    const SETTING_GENERAL_SEND_ORDER_NUMBER = 'send_order_number';
    const SETTING_GENERAL_ORDER_NUMBER_PREFIX = 'order_number_prefix';
    const SETTING_GENERAL_LOG_LEVEL = 'log_level';
    const SETTING_GENERAL_CART_ACTIVE = 'cart_active';
    const SETTING_GENERAL_DETAIL_ACTIVE = 'detail_active';
    const SETTING_GENERAL_LOGIN_ACTIVE = 'login_active';
    const SETTING_GENERAL_OFF_CANVAS_ACTIVE = 'off_canvas_active';

    const SETTING_PUI_CUSTOMER_SERVICE_INSTRUCTIONS = 'customer_service_instructions';

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
