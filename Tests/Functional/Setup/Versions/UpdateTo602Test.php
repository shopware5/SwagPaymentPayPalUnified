<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use Doctrine\DBAL\Connection;
use PDO;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\TranslationTransformer;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo602;
use SwagPaymentPayPalUnified\Tests\Functional\TranslationTestCaseTrait;

class UpdateTo602Test extends TestCase
{
    use TranslationTestCaseTrait;

    const LANGUAGE_SHOP_ID = 2;
    const TRANSLATION_KEY = 1;
    const PAY_LATER_ID = 8;
    const PAYMENT_TYPE = 'config_payment';

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
    private $attributeCrudService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @before
     *
     * @return void
     */
    public function initialize()
    {
        $this->translation = $this->getTranslationService();
        $this->translationTransformer = new TranslationTransformer($this->getContainer()->get('models'));
        $this->attributeCrudService = $this->getContainer()->get('shopware_attribute.crud_service');
        $this->connection = $this->getContainer()->get('dbal_connection');
        $this->modelManager = $this->getContainer()->get('models');
    }

    /**
     * @return void
     */
    public function testUpdate()
    {
        $originalEnGbTranslations = $this->getCurrentEnGbTranslation();

        $this->renamePaymentMethodDescription('anyNewDescription');
        $this->changeEnGbTranslation('PayPal, PAY_LATER', 'Any description');
        $this->attributeCrudService->delete('s_user_attributes', 'swag_paypal_unified_payer_id', true);

        $updater = $this->getUpdateTo602();

        $updater->update();
        $updater->update();

        $translationResult = $this->getCurrentEnGbTranslation();

        static::assertSame('PayPal, Später Bezahlen', $this->getPaymentMethodDescription());
        static::assertSame('PayPal, Pay in 3', $translationResult['description']);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('PayPal PayLater Message', $translationResult['additionalDescription']);
        } else {
            static::assertContains('PayPal PayLater Message', $translationResult['additionalDescription']);
        }

        static::assertNotNull($this->attributeCrudService->get('s_user_attributes', 'swag_paypal_unified_payer_id'));

        $this->changeEnGbTranslation($originalEnGbTranslations['description'], $originalEnGbTranslations['additionalDescription']);
    }

    /**
     * @return UpdateTo602
     */
    private function getUpdateTo602()
    {
        return new UpdateTo602(
            $this->connection,
            $this->translation,
            $this->translationTransformer,
            $this->attributeCrudService,
            $this->modelManager
        );
    }

    /**
     * @param string $description
     * @param string $additionalDescription
     *
     * @return void
     */
    private function changeEnGbTranslation($description, $additionalDescription)
    {
        $this->translation->writeBatch(
            $this->translationTransformer->getTranslations(
                'config_payment',
                [
                    'en_GB' => [
                        PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                            'description' => $description,
                            'additionalDescription' => $additionalDescription,
                        ],
                    ],
                ]
            ),
            true
        );

        $translation = $this->getCurrentEnGbTranslation();

        static::assertSame($description, $translation['description']);
        static::assertSame($additionalDescription, $translation['additionalDescription']);
    }

    /**
     * @return array<string,string>
     */
    private function getCurrentEnGbTranslation()
    {
        return $this->translation->read(self::LANGUAGE_SHOP_ID, self::PAYMENT_TYPE, self::TRANSLATION_KEY)[self::PAY_LATER_ID];
    }

    /**
     * @return string
     */
    private function getPaymentMethodDescription()
    {
        $description = $this->connection->createQueryBuilder()
            ->select(['description'])
            ->from('s_core_paymentmeans')
            ->where('name = :payLaterPaymentMethodName')
            ->setParameter('payLaterPaymentMethodName', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);

        if (!\is_string($description)) {
            static::fail('Cannot read payment method description');
        }

        return $description;
    }

    /**
     * @param string $newDescription
     *
     * @return void
     */
    private function renamePaymentMethodDescription($newDescription)
    {
        $this->connection->createQueryBuilder()
            ->update('s_core_paymentmeans')
            ->set('description', ':newDescription')
            ->where('name = :payLaterPaymentMethodName')
            ->setParameter('newDescription', $newDescription)
            ->setParameter('payLaterPaymentMethodName', PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME)
            ->execute();

        $result = $this->getPaymentMethodDescription();

        static::assertSame($newDescription, $result);
    }
}
