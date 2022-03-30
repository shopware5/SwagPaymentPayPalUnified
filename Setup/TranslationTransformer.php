<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup;

use Doctrine\DBAL\Exception;
use PDO;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;

class TranslationTransformer
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * This method accepts an array of translations structured by locale and
     * object keys:
     *
     * 'en_GB' => [
     *  'SwagPaymentPaypalUnified' => ['description' => 'foo'],
     *  'SwagPaymentPayPalUnifiedSofort' => [...],
     * ],
     * 'de_DE' => [
     *  ...
     * ]
     *
     * It returns an array ready for use with the translation components
     * `writeBatch` method.
     *
     * @param string                                           $objectType
     * @param array<string,array<string,array<string,string>>> $translationData
     *
     * @throws Exception
     *
     * @return list<array{objectlanguage: int, objecttype: string, objectkey: int, objectdata: array<string,string>}>
     *
     * @see \Shopware_Components_Translation::writeBatch
     */
    public function getTranslations($objectType, $translationData)
    {
        $localeRepository = $this->modelManager->getRepository(Locale::class);
        $shopRepository = $this->modelManager->getRepository(Shop::class);

        /** @var Locale[] $locales */
        $locales = array_filter(
            $localeRepository->findBy(['locale' => array_keys($translationData)]),
            static function ($locale) {
                return $locale instanceof Locale;
            }
        );

        $languageIds = array_reduce($locales, static function ($acc, $locale) use ($shopRepository) {
            $shop = $shopRepository->findOneBy([
                'locale' => $locale->getId(),
                'fallback' => null,
            ]);

            if ($shop instanceof Shop) {
                $acc[$locale->getLocale()] = $shop->getId();
            } else {
                // There's currently no shop with the specified language, use the localeId instead
                $acc[$locale->getLocale()] = $locale->getId();
            }

            return $acc;
        }, []);

        $paymentMethodTranslationKeys = $this->getTranslationKeys();

        $translations = [];

        foreach (array_keys($translationData) as $locale) {
            $languageId = $languageIds[$locale];

            $translations = array_merge(
                $translations,
                array_map(function ($paymentMethod, $data) use ($languageId, $objectType, $paymentMethodTranslationKeys) {
                    return [
                        'objectlanguage' => $languageId,
                        'objecttype' => $objectType,
                        'objectkey' => $paymentMethodTranslationKeys[$paymentMethod],
                        'objectdata' => $data,
                    ];
                }, array_keys($translationData[$locale]), array_values($translationData[$locale]))
            );
        }

        return $translations;
    }

    /**
     * @throws Exception
     *
     * @phpstan-return array<string,int>
     *
     * @return array<"PaymentMethodProviderInterface::*_METHOD_NAME",int>
     */
    private function getTranslationKeys()
    {
        return $this->modelManager->getDBALQueryBuilder()
            ->select('name, id')
            ->from('s_core_paymentmeans', 'pm')
            ->execute()
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
