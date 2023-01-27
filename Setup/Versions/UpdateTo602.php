<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Setup\Versions;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
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

    /**
     * @var CrudService|CrudServiceInterface
     */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param CrudService|CrudServiceInterface $crudService
     */
    public function __construct(
        Connection $connection,
        Shopware_Components_Translation $translation,
        TranslationTransformer $translationTransformer,
        $crudService,
        ModelManager $modelManager
    ) {
        $this->translationTransformer = $translationTransformer;
        $this->translation = $translation;
        $this->connection = $connection;
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    /**
     * @return void
     */
    public function update()
    {
        $this->updatePayLaterDescription();
        $this->updatePaymentNameTranslations();
        $this->addCustomerAttributeForQuickOrderer();
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

    /**
     * @return void
     */
    private function addCustomerAttributeForQuickOrderer()
    {
        $this->crudService->update('s_user_attributes', 'swag_paypal_unified_payer_id', TypeMapping::TYPE_STRING);

        $this->modelManager->generateAttributeModels(['s_order_attributes', 's_premium_dispatch_attributes', 's_user_attributes']);
    }
}
