<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Models\Settings\AdvancedCreditDebitCard;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout;
use SwagPaymentPayPalUnified\Models\Settings\General;
use SwagPaymentPayPalUnified\Models\Settings\Installments;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\Models\Settings\Plus;
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
     * @var Shop|null
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
        $this->checkAndDetermineShop();
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
        // If this function is being called in the storefront, the shopId parameter is
        // not required, because it's being provided during the DI.
        if ($shopId === null && $this->shop instanceof Shop) {
            $shopId = $this->shop->getId();
        }

        switch ($settingsType) {
            case SettingsTable::EXPRESS_CHECKOUT:
                $entity = ExpressCheckout::class;
                break;
            case SettingsTable::INSTALLMENTS:
                $entity = Installments::class;
                break;
            case SettingsTable::PLUS:
                $entity = Plus::class;
                break;
            case SettingsTable::PAY_UPON_INVOICE:
                $entity = PayUponInvoice::class;
                break;
            case SettingsTable::ADVANCED_CREDIT_DEBIT_CARD:
                $entity = AdvancedCreditDebitCard::class;
                break;
            default:
                $entity = General::class;
        }

        $settings = $this->modelManager->getRepository($entity)->findOneBy(['shopId' => $shopId]);
        if (!$settings instanceof $entity && $this->shop instanceof Shop && $shopId !== $this->shop->getId()) {
            return $this->getSettings($this->shop->getId());
        }

        return $settings;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RuntimeException
     */
    public function get($column, $settingsTable = SettingsTable::GENERAL)
    {
        if ($this->shop === null) {
            throw new RuntimeException('Could not retrieve a single setting without a shop instance.');
        }

        $table = $this->getTableByType($settingsTable);

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
     * @throws RuntimeException
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
     * @throws RuntimeException
     *
     * @return string
     *
     * @see SettingsTable
     */
    private function getTableByType($settingsType)
    {
        if (\array_key_exists($settingsType, SettingsTable::FULL)) {
            return SettingsTable::FULL[$settingsType];
        }

        throw new RuntimeException('The provided table ' . $settingsType . ' is not supported');
    }

    /**
     * @return void
     */
    private function checkAndDetermineShop()
    {
        if ($this->hasSettings()) {
            return;
        }

        if (!$this->shop instanceof Shop) {
            return;
        }

        $mainShop = $this->shop->getMain();
        if (!$mainShop instanceof Shop) {
            return;
        }

        $mainShopId = (int) $mainShop->getId();
        if ((int) $this->shop->getId() === $mainShopId || empty($mainShopId)) {
            return;
        }

        $this->shop = $mainShop;
    }
}
