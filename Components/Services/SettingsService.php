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

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\DetachedShop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class SettingsService implements SettingsServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var DetachedShop
     */
    private $shop;

    /**
     * @param ModelManager       $modelManager
     * @param DependencyProvider $dependencyProvider
     */
    public function __construct(
        ModelManager $modelManager,
        DependencyProvider $dependencyProvider
    ) {
        $this->modelManager = $modelManager;
        $this->shop = $dependencyProvider->getShop();
        $this->dbalConnection = $modelManager->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings($shopId = null)
    {
        //If this function is being called in the storefront, the shopId parameter is
        //not required, because it's being provided during the DI.
        $shopId = $shopId === null ? $this->shop->getId() : $shopId;

        return $this->modelManager->getRepository(Settings::class)->findOneBy(['shopId' => $shopId]);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function get($column)
    {
        if ($this->shop === null) {
            throw new \RuntimeException('Could not retrieve a single setting without a shop instance.');
        }

        $sql = 'SELECT * FROM `swag_payment_paypal_unified_settings` WHERE `shop_id`=:shopId';

        return $this->dbalConnection->fetchAll($sql, [':shopId' => $this->shop->getId()])[0][$column];
    }

    /**
     * {@inheritdoc}
     */
    public function hasSettings()
    {
        if ($this->shop === null) {
            return false;
        }

        $sql = 'SELECT `id` IS NOT NULL FROM `swag_payment_paypal_unified_settings` WHERE `shop_id`=:shopId';

        return (bool) $this->dbalConnection->fetchColumn($sql, [':shopId' => $this->shop->getId()]);
    }
}
