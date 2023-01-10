<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Setup\Assets\Translations;
use SwagPaymentPayPalUnified\Setup\TranslationTransformer;

class UpdateTo602
{
    /**
     * @var Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var TranslationTransformer
     */
    private $translationTransformer;

    public function __construct(Shopware_Components_Translation $translation, TranslationTransformer $translationTransformer)
    {
        $this->translationTransformer = $translationTransformer;
        $this->translation = $translation;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->updatePaymentNameTranslations();
    }

    /**
     * @return void
     */
    private function updatePaymentNameTranslations()
    {
        $this->translation->writeBatch(
            $this->translationTransformer->getTranslations('config_payment', Translations::CONFIG_PAYMENT_TRANSLATIONS),
            true
        );
    }
}
