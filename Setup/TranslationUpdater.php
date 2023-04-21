<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware_Components_Translation as TranslationWriter;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\Assets\Translations;

class TranslationUpdater
{
    const TRANSLATION_TYPE = 'config_payment';

    const DEFAULT_ISO = 'de_DE';

    const SUPPORTET_LANGUAGE_ISO_ARRAY = ['en_US', 'fr_FR', 'it_IT', 'es_ES', 'en_AU'];

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var TranslationWriter
     */
    private $translationWriter;

    public function __construct(Connection $connection, TranslationWriter $translationWriter)
    {
        $this->connection = $connection;
        $this->translationWriter = $translationWriter;
    }

    /**
     * @param int $localeId
     *
     * @return void
     */
    public function updateTranslationByLocaleId($localeId)
    {
        $translations = $this->getTranslations();
        $localeIso = $this->getLocalIso($localeId);

        if (!\in_array($localeIso, self::SUPPORTET_LANGUAGE_ISO_ARRAY)) {
            return;
        }

        if (!isset($translations[$localeIso])) {
            return;
        }

        if (!isset($translations[$localeIso][PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME])) {
            return;
        }

        $currentTranslation = $translations[$localeIso][PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME];
        $paymentMethodId = $this->getPayLaterPaymentMethodId();
        $shopIds = $this->getShopIdsByLocaleId($localeId);

        foreach ($shopIds as $shopId) {
            if (!empty($this->translationWriter->read($shopId, self::TRANSLATION_TYPE, $paymentMethodId, true))) {
                continue;
            }

            $this->translationWriter->write($shopId, self::TRANSLATION_TYPE, $paymentMethodId, $currentTranslation, true);
        }
    }

    /**
     * @return void
     */
    public function updateTranslationForAllShops()
    {
        $shopLocaleIds = $this->getAllShopLocaleIds();

        foreach ($shopLocaleIds as $localeId) {
            $this->updateTranslationByLocaleId($localeId);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function getTranslations()
    {
        return array_filter(Translations::CONFIG_PAYMENT_TRANSLATIONS, function ($data, $iso) {
            return \in_array($iso, self::SUPPORTET_LANGUAGE_ISO_ARRAY);
        }, \ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param int $localeId
     *
     * @return string
     */
    private function getLocalIso($localeId)
    {
        $localeIso = $this->connection->createQueryBuilder()
            ->select(['locale'])
            ->from('s_core_locales')
            ->where('id = :localeId')
            ->setParameter('localeId', $localeId)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        if (!\is_string($localeIso)) {
            return self::DEFAULT_ISO;
        }

        return $localeIso;
    }

    /**
     * @return int
     */
    private function getPayLaterPaymentMethodId()
    {
        return (int) $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_core_paymentmeans')
            ->where('name = :paymentName')
            ->setParameter('paymentName', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * @param int $localeId
     *
     * @return array<int,mixed>
     */
    private function getShopIdsByLocaleId($localeId)
    {
        return $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_core_shops')
            ->where('locale_id = :localeId')
            ->setParameter('localeId', $localeId)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<int,mixed>
     */
    private function getAllShopLocaleIds()
    {
        return $this->connection->createQueryBuilder()
            ->select(['DISTINCT locale_id'])
            ->from('s_core_shops')
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);
    }
}
