<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Models;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Models\Settings\PayUponInvoice;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class PayUponInvoiceTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;

    /**
     * @return void
     */
    public function testCreateNewPayUponInvoiceSettingHasDefaultValueForShowRatePayHintInMail()
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->getContainer()->get('models');

        $payUponInvoiceSettings = new PayUponInvoice();
        $payUponInvoiceSettings->fromArray([
            'shopId' => 12,
            'onboardingCompleted' => false,
            'sandboxOnboardingCompleted' => false,
            'active' => false,
        ]);

        static::assertTrue($payUponInvoiceSettings->isShowRatePayHintInMail());

        $modelManager->persist($payUponInvoiceSettings);
        $modelManager->flush();

        $savedPayUponInvoiceSettings = $modelManager->find(PayUponInvoice::class, $payUponInvoiceSettings->getId());

        static::assertInstanceOf(PayUponInvoice::class, $savedPayUponInvoiceSettings);
        static::assertTrue($savedPayUponInvoiceSettings->isShowRatePayHintInMail());
    }
}
