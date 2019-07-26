<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\DetachedShop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Models\Settings;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

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
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(
        ModelManager $modelManager,
        DependencyProvider $dependencyProvider
    ) {
        $this->dependencyProvider = $dependencyProvider;

        $this->modelManager = $modelManager;
        $this->dbalConnection = $modelManager->getConnection();

        $this->refreshDependencies();
    }

    /**
     * {@inheritdoc}
     */
    public function refreshDependencies()
    {
        $this->shop = $this->dependencyProvider->getShop();
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings($shopId = null, $settingsType = SettingsTable::GENERAL)
    {
        //If this function is being called in the storefront, the shopId parameter is
        //not required, because it's being provided during the DI.
        $shopId = $shopId === null ? $this->shop->getId() : $shopId;

        switch ($settingsType) {
            case SettingsTable::GENERAL:
                /** @var Settings\General|null $generalSettings */
                $generalSettings = $this->modelManager->getRepository(Settings\General::class)->findOneBy(
                    ['shopId' => $shopId]
                );

                return $generalSettings;
            case SettingsTable::EXPRESS_CHECKOUT:
                /** @var Settings\ExpressCheckout|null $expressSettings */
                $expressSettings = $this->modelManager->getRepository(Settings\ExpressCheckout::class)->findOneBy(
                    ['shopId' => $shopId]
                );

                return $expressSettings;
            case SettingsTable::INSTALLMENTS:
                /** @var Settings\Installments|null $installmentsSettings */
                $installmentsSettings = $this->modelManager->getRepository(Settings\Installments::class)->findOneBy(
                    ['shopId' => $shopId]
                );

                return $installmentsSettings;
            case SettingsTable::PLUS:
                /** @var Settings\Plus|null $plusSettings */
                $plusSettings = $this->modelManager->getRepository(Settings\Plus::class)->findOneBy(
                    ['shopId' => $shopId]
                );

                return $plusSettings;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function get($column, $settingsType = SettingsTable::GENERAL)
    {
        if ($this->shop === null) {
            throw new \RuntimeException('Could not retrieve a single setting without a shop instance.');
        }

        $table = $this->getTableByType($settingsType);

        return $this->dbalConnection->createQueryBuilder()
            ->select($column)
            ->from($table)
            ->where('shop_id = :shopId')
            ->setParameter('shopId', $this->shop->getId())
            ->execute()->fetchColumn();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function hasSettings($settingsType = SettingsTable::GENERAL)
    {
        if ($this->shop === null) {
            return false;
        }

        $table = $this->getTableByType($settingsType);

        return (bool) $this->dbalConnection->createQueryBuilder()
            ->select('id IS NOT NULL')
            ->from($table)
            ->where('shop_id = :shopId')
            ->setParameter('shopId', $this->shop->getId())
            ->execute()->fetchColumn();
    }

    /**
     * A helper function that returns the proper table name by the given settings type.
     *
     * @param string $settingsType
     *
     * @throws \RuntimeException
     *
     * @return string
     *
     * @see SettingsTable
     */
    private function getTableByType($settingsType)
    {
        switch ($settingsType) {
            case SettingsTable::GENERAL:
                return 'swag_payment_paypal_unified_settings_general';
            case SettingsTable::EXPRESS_CHECKOUT:
                return 'swag_payment_paypal_unified_settings_express';
            case SettingsTable::INSTALLMENTS:
                return 'swag_payment_paypal_unified_settings_installments';
            case SettingsTable::PLUS:
                return 'swag_payment_paypal_unified_settings_plus';
            default:
                throw new \RuntimeException('The provided table ' . $settingsType . ' is not supported');
                break;
        }
    }
}
