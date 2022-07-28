<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents;

use Enlight_Event_EventArgs;
use Enlight_Hook_HookArgs;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Shopware_Components_Document;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Subscriber\Documents\Invoice;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\SettingsHelperTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;

class InvoiceSubscriberPuiTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use ContainerTrait;
    use ShopRegistrationTrait;
    use SettingsHelperTrait;

    /**
     * @return void
     */
    public function testOnBeforeRenderDocument()
    {
        $this->installOrder();

        $orderId = 60999;
        $documentId = 1;
        $document = Shopware_Components_Document::initDocument($orderId, $documentId);

        $hookArgs = $this->createShopwareVersionRelatedHookArgs($document);

        $subscriber = $this->getSubscriber();

        $subscriber->onBeforeRenderDocument($hookArgs);

        $result = $document->_template->tpl_vars;

        static::assertArrayHasKey('payUponInvoiceRatepayInstructions', $result);
        static::assertArrayHasKey('PayPalUnifiedInvoiceInstruction', $result);
        static::assertArrayHasKey('bankName', $result['PayPalUnifiedInvoiceInstruction']->value);
        static::assertArrayHasKey('accountHolder', $result['PayPalUnifiedInvoiceInstruction']->value);
        static::assertArrayHasKey('iban', $result['PayPalUnifiedInvoiceInstruction']->value);
        static::assertArrayHasKey('bic', $result['PayPalUnifiedInvoiceInstruction']->value);
        static::assertArrayHasKey('amount', $result['PayPalUnifiedInvoiceInstruction']->value);
        static::assertArrayHasKey('reference', $result['PayPalUnifiedInvoiceInstruction']->value);

        static::assertSame('Test Sparkasse - Berlin', $result['PayPalUnifiedInvoiceInstruction']->value['bankName']);
        static::assertSame('Paypal - Ratepay GmbH - Test Bank Account', $result['PayPalUnifiedInvoiceInstruction']->value['accountHolder']);
        static::assertSame('DE12345678901234567890', $result['PayPalUnifiedInvoiceInstruction']->value['iban']);
        static::assertSame('BELADEBEXXX', $result['PayPalUnifiedInvoiceInstruction']->value['bic']);
        static::assertSame('45.94', $result['PayPalUnifiedInvoiceInstruction']->value['amount']);
        static::assertSame('7SW1992983152031M', $result['PayPalUnifiedInvoiceInstruction']->value['reference']);
    }

    /**
     * @return void
     */
    public function testOnFilterMailVariables()
    {
        $eventArgs = $this->createEventArgs();

        $subscriber = $this->getSubscriber();

        $result = $subscriber->onFilterMailVariables($eventArgs);

        static::assertArrayHasKey('paypalUnifiedRatePayHint', $result['additional']);
        static::assertStringStartsWith('Bitte beachten Sie, dass Shopware Demo', $result['additional']['paypalUnifiedRatePayHint']);
        static::assertNull($result['additional']['payment']['additionaldescription']);
    }

    /**
     * @return void
     */
    public function testOnFilterMailVariablesShouldAddAdditionalDescriptionToPayment()
    {
        $this->insertPayUponInvoiceSettingsFromArray([
            'active' => 1,
        ]);
        $eventArgs = $this->createEventArgs();

        $subscriber = $this->getSubscriber();

        $result = $subscriber->onFilterMailVariables($eventArgs);

        static::assertArrayHasKey('paypalUnifiedRatePayHint', $result['additional']);
        static::assertStringStartsWith('Bitte beachten Sie, dass Shopware Demo', $result['additional']['paypalUnifiedRatePayHint']);
        static::assertStringStartsWith('Bitte beachten Sie, dass Shopware Demo', $result['additional']['payment']['additionaldescription']);
    }

    /**
     * @return Invoice
     */
    private function getSubscriber()
    {
        $container = $this->getContainer();

        return new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('snippets'),
            new Shopware_Components_Translation($container->get('dbal_connection'), $container),
            Shopware()->Container()->get('template'),
            Shopware()->Container()->get('paypal_unified.payment_method_provider'),
            Shopware()->Container()->get('paypal_unified.settings_service')
        );
    }

    /**
     * @return void
     */
    private function installOrder()
    {
        $sql = file_get_contents(__DIR__ . '/_fixtures/pui_order.sql');
        static::assertTrue(\is_string($sql));

        $this->getContainer()->get('dbal_connection')->exec($sql);
    }

    /**
     * @return Enlight_Event_EventArgs
     */
    private function createEventArgs()
    {
        $eventArgs = new Enlight_Event_EventArgs();
        $eventArgs->setReturn([
            'additional' => [
                'payment' => [
                    'name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME,
                ],
            ],
        ]);

        return $eventArgs;
    }

    /**
     * @return Enlight_Hook_HookArgs
     */
    private function createShopwareVersionRelatedHookArgs(Shopware_Components_Document $document)
    {
        $reflectionConstructor = (new \ReflectionClass(Enlight_Hook_HookArgs::class))->getConstructor();
        static::assertInstanceOf(ReflectionMethod::class, $reflectionConstructor);
        $numberOfParameters = $reflectionConstructor->getNumberOfParameters();

        if ($numberOfParameters > 1) {
            $hookArgs = new Enlight_Hook_HookArgs($document, 'assignValues');
        } else {
            // "class" is the key for subject.
            $hookArgs = new Enlight_Hook_HookArgs(['class' => $document]);
        }

        return $hookArgs;
    }
}
