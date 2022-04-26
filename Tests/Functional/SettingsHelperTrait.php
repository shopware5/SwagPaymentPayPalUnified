<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

trait SettingsHelperTrait
{
    /**
     * /**
     * @var CamelCaseToSnakeCaseNameConverter|null
     */
    private $camelCaseToSnakeCaseConverter = null;

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function insertGeneralSettingsFromArray(array $data)
    {
        $default = [
            'id' => null,
            'shop_id' => 1,
            'active' => 0,
            'client_id' => null,
            'client_secret' => null,
            'sandbox_client_id' => null,
            'sandbox_client_secret' => null,
            'sandbox' => 1,
            'show_sidebar_logo' => 0,
            'brand_name' => null,
            'landing_page_type' => null,
            'send_order_number' => 0,
            'order_number_prefix' => null,
            'use_in_context' => 0,
            'display_errors' => 0,
            'advertise_returns' => 0,
            'use_smart_payment_buttons' => 0,
            'submit_cart' => 0,
            'intent' => 'CAPTURE',
            'button_style_color' => 'gold',
            'button_style_shape' => 'rectangle',
            'button_style_size' => 'medium',
            'button_locale' => null,
            'paypal_payer_id' => null,
            'sandbox_paypal_payer_id' => null,
            'order_status_on_failed_payment' => -1,
            'payment_status_on_failed_payment' => 35,
        ];

        $this->addOrUpdateSettings('swag_payment_paypal_unified_settings_general', $default, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function insertPlusSettingsFromArray(array $data)
    {
        $default = [
            'id' => null,
            'shop_id' => 1,
            'active' => 0,
            'restyle' => 1,
            'integrate_third_party_methods' => 0,
            'payment_name' => null,
            'payment_description' => null,
            'ppcp_active' => 0,
            'sandbox_ppcp_active' => 0,
        ];

        $this->addOrUpdateSettings('swag_payment_paypal_unified_settings_plus', $default, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function insertAdvancedCreditDebitCardSettingsFromArray(array $data)
    {
        $default = [
            'id' => null,
            'shop_id' => 1,
            'onboarding_completed' => 0,
            'sandbox_onboarding_completed' => 0,
            'active' => 0,
        ];

        $this->addOrUpdateSettings('swag_payment_paypal_unified_settings_advanced_credit_debit_card', $default, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function insertExpressCheckoutSettingsFromArray(array $data)
    {
        $default = [
            'id' => null,
            'shop_id' => 1,
            'detail_active' => 0,
            'cart_active' => 0,
            'off_canvas_active' => 0,
            'login_active' => 0,
            'listing_active' => 0,
            'button_style_color' => 0,
            'button_style_shape' => 0,
            'button_style_size' => 0,
            'button_locale' => 'en_US',
            'submit_cart' => 0,
        ];

        $this->addOrUpdateSettings('swag_payment_paypal_unified_settings_express', $default, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function insertInstallmentsSettingsFromArray(array $data)
    {
        $default = [
            'id' => null,
            'shop_id' => 1,
            'advertise_installments' => 0,
        ];

        $this->addOrUpdateSettings('swag_payment_paypal_unified_settings_installments', $default, $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function insertPayUponInvoiceSettingsFromArray(array $data)
    {
        $default = [
            'id' => null,
            'shop_id' => 1,
            'onboarding_completed' => 0,
            'sandbox_onboarding_completed' => 0,
            'active' => 0,
            'customer_service_instructions' => null,
        ];

        $this->addOrUpdateSettings('swag_payment_paypal_unified_settings_pay_upon_invoice', $default, $data);
    }

    /**
     * @return void
     */
    public function clearSettingsTables()
    {
        $this->getDbConnection()->exec(
            'DELETE FROM swag_payment_paypal_unified_settings_express WHERE 1;
            DELETE FROM swag_payment_paypal_unified_settings_general WHERE 1;
            DELETE FROM swag_payment_paypal_unified_settings_installments WHERE 1;
            DELETE FROM swag_payment_paypal_unified_settings_plus WHERE 1;
            DELETE FROM swag_payment_paypal_unified_payment_instruction WHERE 1;
            DELETE FROM swag_payment_paypal_unified_settings_advanced_credit_debit_card WHERE 1;
            DELETE FROM swag_payment_paypal_unified_settings_pay_upon_invoice WHERE 1;'
        );
    }

    /**
     * @param string              $tableName
     * @param array<string,mixed> $default
     * @param array<string,mixed> $data
     *
     * @return void
     */
    private function addOrUpdateSettings($tableName, array $default, array $data)
    {
        $dataResult = $this->applyData($data, $default);

        $settingsId = $this->getSettingsByShopId($tableName, $dataResult['shop_id']);

        $dataResult['id'] = $settingsId > 0 ? $settingsId : null;

        $this->insertSettings($tableName, $dataResult);
    }

    /**
     * @param string $tableName
     * @param int    $shopId
     *
     * @return int
     */
    private function getSettingsByShopId($tableName, $shopId)
    {
        $sql = "SELECT id FROM $tableName WHERE shop_id = ?";

        return (int) $this->getDbConnection()->fetchOne($sql, [$shopId]);
    }

    /**
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private function getDbConnection()
    {
        return Shopware()->Container()->get('db');
    }

    /**
     * @param string              $tableName
     * @param array<string|mixed> $data
     *
     * @return void
     */
    private function insertSettings($tableName, array $data)
    {
        if ($data['id'] !== null) {
            $this->getDbConnection()->update($tableName, $data);

            return;
        }

        $this->getDbConnection()->insert($tableName, $data);
    }

    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $default
     *
     * @return array<string,mixed>
     */
    private function applyData(array $data, array $default)
    {
        foreach ($data as $columnName => $value) {
            $snakeCaseKey = $this->convertCamelToSnakeCase($columnName);
            $default[$snakeCaseKey] = $value;
        }

        return $default;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function convertCamelToSnakeCase($string)
    {
        if (!$this->camelCaseToSnakeCaseConverter instanceof CamelCaseToSnakeCaseNameConverter) {
            $this->camelCaseToSnakeCaseConverter = new CamelCaseToSnakeCaseNameConverter();
        }

        return $this->camelCaseToSnakeCaseConverter->normalize($string);
    }
}
