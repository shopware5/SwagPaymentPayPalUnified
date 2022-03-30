<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents;

use Enlight_Event_EventArgs;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\Amount;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Instruction\RecipientBanking;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\Subscriber\Documents\Invoice;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\PayPalUnifiedPaymentIdTrait;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock\HookArgsWithCorrectPaymentId;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock\HookArgsWithoutSubject;
use SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock\HookArgsWithWrongPaymentId;

class InvoiceSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use PayPalUnifiedPaymentIdTrait;

    const TEST_ORDER_NUMBER = '20001';
    const TEST_AMOUNT_VALUE = 50.5;
    const TEST_DUE_DATE = '01-01-2000';
    const TEST_REFERENCE = 'TEST_REFERENCE_NUMBER';
    const TEST_BANK_IBAN = 'TEST_IBAN';
    const TEST_BANK_BIC = 'TEST_BIC';
    const TEST_BANK_BANK_NAME = 'TEST_BANK';
    const TEST_BANK_ACCOUNT_HOLDER = 'TEST_ACCOUNT_HOLDER';

    public function testConstruct()
    {
        $subscriber = $this->getSubscriber();
        static::assertNotNull($subscriber);
    }

    public function testConstructWithoutTranslator()
    {
        $translatorConstructor = (new ReflectionClass('Shopware_Components_Translation'))->getConstructor();
        if ($translatorConstructor && !empty($translatorConstructor->getParameters())) {
            static::markTestSkipped('Test makes only sense if the translation component has no constructor parameters');
        }

        $subscriber = new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('snippets'),
            null,
            Shopware()->Container()->get('template'),
            Shopware()->Container()->get('paypal_unified.payment_method_provider')
        );
        static::assertNotNull($subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $events = Invoice::getSubscribedEvents();

        static::assertCount(2, $events);
        static::assertSame('onBeforeRenderDocument', $events['Shopware_Components_Document::assignValues::after']);
        static::assertSame('onFilterMailVariables', $events['Shopware_Modules_Order_SendMail_FilterVariables']);
    }

    public function testOnBeforeRenderDocumentReturnsWhenNoDocumentWasGiven()
    {
        $subscriber = $this->getSubscriber();
        $hookArgs = new HookArgsWithoutSubject();

        static::assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function testOnBeforeRenderDocumentReturnsWhenWrongPaymentIdWasGiven()
    {
        $subscriber = $this->getSubscriber();

        $hookArgs = new HookArgsWithWrongPaymentId();

        static::assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function testOnBeforeRenderDocumentReturnsWhenWrongPaymentType()
    {
        $subscriber = $this->getSubscriber();

        $this->updateOrderPaymentId(15, $this->getUnifiedPaymentId());
        $hookArgs = new HookArgsWithCorrectPaymentId(Shopware()->Container()->has('shopware.benchmark_bundle.collector'));

        static::assertNull($subscriber->onBeforeRenderDocument($hookArgs));
    }

    public function testOnBeforeRenderDocumentHandleDocument()
    {
        $subscriber = $this->getSubscriber();

        $this->updateOrderPaymentId(15, $this->getUnifiedPaymentId());
        $this->insertTestData();

        $hookArgs = new HookArgsWithCorrectPaymentId(Shopware()->Container()->has('shopware.benchmark_bundle.collector'));

        $subscriber->onBeforeRenderDocument($hookArgs);

        $view = $hookArgs->getTemplate();

        static::assertNotNull($view->getVariable('PayPalUnifiedInvoiceInstruction'));
    }

    public function testOnFilterMailVariables()
    {
        $subscriber = $this->getSubscriber();
        $args = new Enlight_Event_EventArgs();

        $template = [
            'additional' => [
                'payment' => [
                    'name' => PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME,
                    'additionaldescription' => '{link file="frontend/_public/src/img/sidebar-paypal-generic.png" fullPath}',
                ],
            ],
        ];

        $args->setReturn($template);
        $result = $subscriber->onFilterMailVariables($args);

        static::assertStringEndsWith(
            'custom/plugins/SwagPaymentPayPalUnified/Resources/views/frontend/_public/src/img/sidebar-paypal-generic.png',
            $result['additional']['payment']['additionaldescription']
        );
    }

    public function testOnFilterMailVariablesShouldNotBeRendered()
    {
        $subscriber = $this->getSubscriber();
        $args = new Enlight_Event_EventArgs();

        $template = [
            'additional' => [
                'payment' => [
                    'name' => 'SomeOtherPaymentMethod',
                    'additionaldescription' => '{link file="frontend/_public/src/img/sidebar-paypal-generic.png" fullPath}',
                ],
            ],
        ];

        $args->setReturn($template);
        $result = $subscriber->onFilterMailVariables($args);

        static::assertStringEndsWith(
            '{link file="frontend/_public/src/img/sidebar-paypal-generic.png" fullPath}',
            $result['additional']['payment']['additionaldescription']
        );
    }

    private function getSubscriber()
    {
        return new Invoice(
            Shopware()->Container()->get('paypal_unified.payment_instruction_service'),
            Shopware()->Container()->get('dbal_connection'),
            Shopware()->Container()->get('snippets'),
            $this->getTranslationService(),
            Shopware()->Container()->get('template'),
            Shopware()->Container()->get('paypal_unified.payment_method_provider')
        );
    }

    private function insertTestData()
    {
        $instructions = new PaymentInstruction();
        $instructions->setDueDate(self::TEST_DUE_DATE);
        $instructions->setReferenceNumber(self::TEST_REFERENCE);

        $testAmount = new Amount();
        $testAmount->setValue(self::TEST_AMOUNT_VALUE);
        $instructions->setAmount($testAmount);

        $testBanking = new RecipientBanking();
        $testBanking->setIban(self::TEST_BANK_IBAN);
        $testBanking->setBic(self::TEST_BANK_BIC);
        $testBanking->setBankName(self::TEST_BANK_BANK_NAME);
        $testBanking->setAccountHolderName(self::TEST_BANK_ACCOUNT_HOLDER);
        $instructions->setRecipientBanking($testBanking);

        $instructionsService = Shopware()->Container()->get('paypal_unified.payment_instruction_service');
        $instructionsService->createInstructions(self::TEST_ORDER_NUMBER, $instructions);

        $sql = "UPDATE s_order_attributes SET swag_paypal_unified_payment_type='PayPalPlusInvoice' WHERE orderID=15";
        $db = Shopware()->Container()->get('dbal_connection');
        $db->executeUpdate($sql);
    }

    /**
     * @param int $orderId
     * @param int $paymentId
     */
    private function updateOrderPaymentId($orderId, $paymentId)
    {
        $db = Shopware()->Container()->get('dbal_connection');

        $sql = 'UPDATE s_order SET paymentID=:paymentId WHERE id=:orderId';
        $db->executeUpdate($sql, [
            ':paymentId' => $paymentId,
            ':orderId' => $orderId,
        ]);
    }

    private function getTranslationService()
    {
        $container = Shopware()->Container();

        if ($container->initialized('translation')) {
            return $container->get('translation');
        }

        return new Shopware_Components_Translation($container->get('dbal_connection'), $container);
    }
}
