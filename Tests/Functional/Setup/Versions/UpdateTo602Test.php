<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Setup;

use PHPUnit\Framework\TestCase;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Setup\TranslationTransformer;
use SwagPaymentPayPalUnified\Setup\Versions\UpdateTo602;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\TranslationTestCaseTrait;

class UpdateTo602Test extends TestCase
{
    use DatabaseTestCaseTrait;
    use TranslationTestCaseTrait;

    /**
     * @var Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var TranslationTransformer
     */
    private $translationTransformer;

    /**
     * @before
     *
     * @return void
     */
    public function initialize()
    {
        $this->translation = $this->getTranslationService();
        $this->translationTransformer = new TranslationTransformer($this->getContainer()->get('models'));
    }

    /**
     * @return void
     */
    public function testUpdate()
    {
        $this->renamePayLater();

        $updater = $this->getUpdateTo602();

        $updater->update();
        $updater->update();

        $translationResult = $this->getCurrentEnGbTranslation();

        static::assertSame('PayPal, Pay in 3', $translationResult['description']);

        if (\method_exists($this, 'assertStringContainsString')) {
            static::assertStringContainsString('PayPal PayLater Message', $translationResult['additionalDescription']);
        } else {
            static::assertContains('PayPal PayLater Message', $translationResult['additionalDescription']);
        }
    }

    /**
     * @return UpdateTo602
     */
    private function getUpdateTo602()
    {
        return new UpdateTo602($this->translation, $this->translationTransformer);
    }

    /**
     * @return void
     */
    private function renamePayLater()
    {
        $this->translation->writeBatch(
            $this->translationTransformer->getTranslations(
                'config_payment',
                [
                    'en_GB' => [
                        PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_LATER_METHOD_NAME => [
                            'description' => 'PayPal, PAY_LATER',
                            'additionalDescription' => 'Any description',
                        ],
                    ],
                ]
            ),
            true
        );

        $translation = $this->getCurrentEnGbTranslation();

        static::assertSame('PayPal, PAY_LATER', $translation['description']);
        static::assertSame('Any description', $translation['additionalDescription']);
    }

    /**
     * @return array<string,string>
     */
    private function getCurrentEnGbTranslation()
    {
        return $this->translation->read(2, 'config_payment', 1)[8];
    }
}
