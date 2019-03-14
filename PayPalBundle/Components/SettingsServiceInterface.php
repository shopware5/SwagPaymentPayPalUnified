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
