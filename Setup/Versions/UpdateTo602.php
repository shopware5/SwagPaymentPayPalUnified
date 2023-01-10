<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\Assets\Translations;
use SwagPaymentPayPalUnified\Setup\TranslationTransformer;

class UpdateTo602
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var TranslationTransformer
     */
    private $translationTransformer;

    public function __construct(
        Connection $connection,
        Shopware_Components_Translation $translation,
        TranslationTransformer $translationTransformer
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->translation = $translation;
        $this->connection = $connection;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->updatePayLaterDescription();
        $this->updatePaymentNameTranslations();
    }

    /**
     * @return void
     */
    private function updatePayLaterDescription()
    {
        $this->connection->createQueryBuilder()
            ->update('s_core_paymentmeans')
            ->set('description', ':newPayLaterDescription')
            ->where('name = :payLaterPaymentMethodName')
            ->setParameter('newPayLaterDescription', Translations::CONFIG_PAYMENT_TRANSLATIONS['de_DE'][PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME]['description'])
            ->setParameter('payLaterPaymentMethodName', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME)
            ->execute();
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
