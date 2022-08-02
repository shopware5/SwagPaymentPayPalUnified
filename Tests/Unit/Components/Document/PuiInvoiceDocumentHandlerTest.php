<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Document;

use Enlight_Template_Manager;
use PHPUnit\Framework\TestCase;
use Shopware_Components_Translation;
use Smarty_Data;
use SwagPaymentPayPalUnified\Components\Document\PuiInvoiceDocumentHandler;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ReflectionHelperTrait;

class PuiInvoiceDocumentHandlerTest extends TestCase
{
    use ContainerTrait;
    use ReflectionHelperTrait;
    use DatabaseTestCaseTrait;

    /**
     * @return void
     */
    public function testGetNotification()
    {
        $documentHandler = $this->getPuiInvoiceDocumentHandler();
        $method = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'getNotification');

        $result = $method->invoke($documentHandler, $this->getTemplate());

        static::assertStringStartsWith('Bitte beachten Sie, dass Shopware Demo', $result);
    }

    /**
     * @return void
     */
    public function testRenderTemplateString()
    {
        $documentHandler = $this->getPuiInvoiceDocumentHandler();
        $method = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'renderTemplateString');

        $result = $method->invokeArgs($documentHandler, [$this->getTemplate(), '{config name=shopName}']);

        static::assertSame('Shopware Demo', $result);
    }

    /**
     * @return void
     */
    public function testPrepareTemplateString()
    {
        $documentHandler = $this->getPuiInvoiceDocumentHandler();
        $method = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'prepareTemplateString');

        $result = $method->invoke($documentHandler, 'AnyString');

        static::assertSame('string:AnyString', $result);
    }

    /**
     * @return void
     */
    public function testGetRawTemplate()
    {
        $documentHandler = $this->getPuiInvoiceDocumentHandler();
        $method = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'getRawTemplate');

        $result = $method->invoke($documentHandler, 1);

        static::assertTrue(\is_array($result));
        static::assertSame('PayPal_Unified_Ratepay_Instructions', $result['PayPal_Unified_Ratepay_Instructions']['name']);
        static::assertSame('PayPal_Unified_Instructions_Footer', $result['PayPal_Unified_Instructions_Footer']['name']);
        static::assertStringStartsWith('<div class="unified_payment_note">', $result['PayPal_Unified_Ratepay_Instructions']['value']);
        static::assertStringStartsWith('<table style="height: 90px;" border="0" width="100%">', $result['PayPal_Unified_Instructions_Footer']['value']);
    }

    /**
     * @return void
     */
    public function testAssignDataToDocumentTemplate()
    {
        $documentHandler = $this->getPuiInvoiceDocumentHandler();
        $method = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'assignDataToDocumentTemplate');
        $template = $this->getTemplate();
        $ordernumber = '295678932';
        $instructionsSql = file_get_contents(__DIR__ . '/_fixtures/payment_instructions.sql');
        static::assertTrue(\is_string($instructionsSql));
        $this->getContainer()->get('dbal_connection')->exec($instructionsSql);

        $method->invokeArgs($documentHandler, [$template, $ordernumber]);

        $result = $template->tpl_vars;

        static::assertNotEmpty($result['payUponInvoiceRatepayInstructions']);
        static::assertNotEmpty($result['PayPalUnifiedInvoiceInstruction']);
    }

    /**
     * @return void
     */
    public function testPrepareContainers()
    {
        $documentHandler = $this->getPuiInvoiceDocumentHandler();

        $methodGetRawTemplate = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'getRawTemplate');
        $methodPrepareContainers = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'PrepareContainers');

        $view = new Smarty_Data();
        $view->assign($this->getViewContainers());
        $rawTemplate = $methodGetRawTemplate->invoke($documentHandler, 1);
        $template = $this->getTemplate();

        $result = $methodPrepareContainers->invokeArgs($documentHandler, [$view, $rawTemplate, $template]);

        static::assertSame('PayPal_Unified_Ratepay_Instructions', $result['Content_Info']['name']);
        static::assertStringStartsWith('<div class="unified_payment_note">', $result['Content_Info']['value']);
        static::assertStringStartsWith('}.unified_payment_instruction', $result['Content_Info']['style']);
        static::assertStringStartsWith('<table style="height: 90px;" border="0" width="100%">', $result['Footer']['value']);
        static::assertStringStartsWith('width: 170mm;', $result['Footer']['style']);
    }

    /**
     * @return void
     */
    public function testTranslateFooter()
    {
        $sql = 'SELECT id FROM s_core_translations WHERE `objecttype` LIKE "documents"';
        $documentTranslationId = $this->getContainer()->get('dbal_connection')->fetchAll($sql);

        if (!$documentTranslationId) {
            $sql = file_get_contents(__DIR__ . '/_fixtures/insert_translations.sql');
        } else {
            $sql = file_get_contents(__DIR__ . '/_fixtures/update_translations.sql');
        }

        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $documentHandler = $this->getPuiInvoiceDocumentHandler();

        $methodTranslateFooter = $this->getReflectionMethod(PuiInvoiceDocumentHandler::class, 'translateFooter');

        $view = new Smarty_Data();
        $view->assign('Order', ['_order' => ['language' => 2]]);

        $result = $methodTranslateFooter->invokeArgs($documentHandler, [$view, ['PayPal_Unified_Ratepay_Instructions' => ['documentID' => 1]]]);

        static::assertSame('<p>Content info content Pay upon Invoice</p>', $result['PayPal_Unified_Ratepay_Instructions']['value']);
        static::assertSame('Content info style PayPal Pay upon Invoice', $result['PayPal_Unified_Ratepay_Instructions']['style']);
        static::assertSame('<p>Footer content PayPal Plus Invoice and Pay upon Invoice</p>', $result['PayPal_Unified_Instructions_Footer']['value']);
        static::assertSame('Footer style PayPal Plus Invoice and Pay upon Invoice', $result['PayPal_Unified_Instructions_Footer']['style']);
    }

    /**
     * @return array<string,mixed>
     */
    private function getViewContainers()
    {
        return [
            'Containers' => [
                'Content_Info' => [
                    'value' => null,
                    'style' => null,
                ],
            ],
            'Footer' => null,
        ];
    }

    /**
     * @return PuiInvoiceDocumentHandler
     */
    private function getPuiInvoiceDocumentHandler()
    {
        $translator = new Shopware_Components_Translation(
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()
        );

        return new PuiInvoiceDocumentHandler(
            $this->getContainer()->get('snippets'),
            $this->getContainer()->get('dbal_connection'),
            $this->getContainer()->get('paypal_unified.payment_instruction_service'),
            $translator
        );
    }

    /**
     * @return Enlight_Template_Manager
     */
    private function getTemplate()
    {
        return $this->getContainer()->get('template_factory')->factory(
            $this->getContainer()->get('events'),
            $this->getContainer()->get('snippet_resource'),
            $this->getContainer()->get('shopware.escaper'),
            [],
            [],
            []
        );
    }
}
