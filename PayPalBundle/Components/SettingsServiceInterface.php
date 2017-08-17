<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
     * @return null|ModelEntity
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
     * @return mixed
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
