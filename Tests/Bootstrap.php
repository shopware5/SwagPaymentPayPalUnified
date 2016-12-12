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

require __DIR__ . '/../../../../tests/Functional/bootstrap.php';

class PayPalUnifiedTestKernel extends TestKernel
{
    public static function start()
    {
        parent::start();

        if (!self::isPluginInstalledAndActivated()) {
            die('Error: The plugin is not installed or activated, tests aborted!');
        }

        Shopware()->Loader()->registerNamespace('SwagPaymentPayPalUnified', __DIR__ . '/../');
        Shopware()->Loader()->registerNamespace(
            'Shopware\CustomModels',
            __DIR__ . '/../' . 'Models/'
        );
    }

    /**
     * @return bool
     */
    private static function isPluginInstalledAndActivated()
    {
        /** @var \Doctrine\DBAL\Connection $db */
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = "SELECT active FROM s_core_plugins WHERE name='SwagPaymentPayPalUnified'";
        $active = $db->fetchColumn($sql);

        return (bool) $active;
    }
}

PayPalUnifiedTestKernel::start();
