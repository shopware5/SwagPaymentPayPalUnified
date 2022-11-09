<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\Components\Document;

use Doctrine\DBAL\Connection;
use Enlight_Components_Snippet_Namespace;
use Enlight_Template_Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware_Components_Document;
use Shopware_Components_Snippet_Manager as SnippetManager;
use Shopware_Components_Translation;
use Smarty_Data;
use SwagPaymentPayPalUnified\Components\Document\InvoiceDocumentHandler;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\Models\PaymentInstruction;

class InvoiceDocumentHandlerTest extends TestCase
{
    const STATIC_SNIPPET = '37e0060e-5007-46b2-8b67-eded1f0967af';
    const STATIC_PAYMENT_INSTRUCTION = '21b61d4e-5bfe-446e-a53e-36ba6ca2ed4d';

    /**
     * @var MockObject|PaymentInstructionService
     */
    private $paymentInstructionService;

    /**
     * @var MockObject|Connection
     */
    private $connection;

    /**
     * @var MockObject|SnippetManager
     */
    private $snippetManager;

    /**
     * @var MockObject|Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var MockObject|Shopware_Components_Document
     */
    private $document;

    /**
     * @var MockObject|Smarty_Data
     */
    private $view;

    /**
     * @var Enlight_Template_Manager|MockObject
     */
    private $template;

    /**
     * @before
     *
     * @return void
     */
    public function init()
    {
        $this->paymentInstructionService = static::createMock(PaymentInstructionService::class);
        $this->connection = static::createMock(Connection::class);
        $this->snippetManager = static::createMock(SnippetManager::class);
        $this->translation = static::createMock(Shopware_Components_Translation::class);
        $this->document = static::createMock(Shopware_Components_Document::class);
        $this->view = static::createMock(Smarty_Data::class);
        $this->template = static::createMock(Enlight_Template_Manager::class);
    }

    /**
     * @return void
     */
    public function testHandleDocumentAbortsIfDocumentIsEmpty()
    {
        $this->givenDocumentHasAView();

        $this->expectViewNotToBeExamined();

        $this->getInvoiceDocumentHandler()->handleDocument(
            'f141bfea-fe7e-47e8-89f8-8abc5378432c',
            $this->document
        );
    }

    /**
     * @return void
     */
    public function testHandleDocumentOverwritesPaymentName()
    {
        $this->givenDocumentHasAView();
        $this->givenViewHasOrderData();

        $this->givenDocumentHasATemplate();
        $this->givenDocumentHasPayPalUnifiedBoxes();

        $this->givenSnippetManagerReturnsStaticValue(self::STATIC_SNIPPET);

        $this->expectPaymentNameToBe(self::STATIC_SNIPPET);

        $this->getInvoiceDocumentHandler()->handleDocument(
            '1cc2fac4-9967-4339-9682-c97e47889161',
            $this->document
        );
    }

    /**
     * @return void
     */
    public function testHandleDocumentAddsInstructionsIfPresent()
    {
        $this->givenDocumentHasAView();
        $this->givenViewHasOrderData();

        $this->givenDocumentHasATemplate();
        $this->givenDocumentHasPayPalUnifiedBoxes();
        $this->givenSnippetManagerReturnsStaticValue(self::STATIC_SNIPPET);

        $this->givenThereIsAnInstruction([self::STATIC_PAYMENT_INSTRUCTION]);

        $this->expectTemplateToReceiveInstruction([self::STATIC_PAYMENT_INSTRUCTION]);

        $this->getInvoiceDocumentHandler()->handleDocument(
            '3c28fec0-dd49-404c-8806-54c2255295d1',
            $this->document
        );
    }

    /**
     * @return InvoiceDocumentHandler
     */
    private function getInvoiceDocumentHandler(
        PaymentInstructionService $paymentInstructionService = null,
        Connection $connection = null,
        SnippetManager $snippetManager = null,
        Shopware_Components_Translation $translation = null
    ) {
        return new InvoiceDocumentHandler(
            $paymentInstructionService ?: $this->paymentInstructionService,
            $connection ?: $this->connection,
            $snippetManager ?: $this->snippetManager,
            $translation ?: $this->translation
        );
    }

    /**
     * @param array<string, array<string, string>> $orderData
     *
     * @return void
     */
    private function givenViewHasOrderData($orderData = null)
    {
        if ($orderData === null) {
            $orderData = [
                '_payment' => [
                    'description' => 'db7172db-501c-41a9-a157-0986abf21505',
                ],
            ];
        }

        $this->view->method('getTemplateVars')
            ->willReturnMap([
                ['Order', null, true, $orderData],
            ]);
    }

    /**
     * @return void
     */
    private function givenDocumentHasAView(Smarty_Data $view = null)
    {
        $this->document->_view = $view ?: $this->view;
    }

    /**
     * @return void
     */
    private function givenDocumentHasATemplate(Enlight_Template_Manager $template = null)
    {
        $this->document->_template = $template ?: $this->template;
    }

    /**
     * @return void
     */
    private function givenDocumentHasPayPalUnifiedBoxes()
    {
        $this->connection->method('fetchColumn')
            ->with(
                static::stringContains('SELECT id FROM s_core_documents_box WHERE documentID = ? AND `name` = ?;'),
                static::callback(static function ($parameters) {
                    static::assertTrue(\is_array($parameters));
                    static::assertCount(2, $parameters);

                    static::assertSame('PayPal_Unified_Instructions_Content', $parameters[1]);

                    return true;
                })
            )
            ->willReturn(1);
    }

    /**
     * @param string $value
     *
     * @return void
     */
    private function givenSnippetManagerReturnsStaticValue($value)
    {
        $namespace = static::createMock(Enlight_Components_Snippet_Namespace::class);

        $namespace->method('get')
            ->willReturn($value);

        $this->snippetManager->method('getNamespace')
            ->withAnyParameters()
            ->willReturn($namespace);
    }

    /**
     * @return void
     */
    private function expectViewNotToBeExamined()
    {
        $this->view->expects(static::never())->method('getTemplateVars');
    }

    /**
     * @param string $val
     *
     * @return void
     */
    private function expectPaymentNameToBe($val)
    {
        $this->view->expects(static::atLeastOnce())
            ->method('assign')
            ->withConsecutive([
                'Order',
                [
                    '_payment' => [
                        'description' => $val,
                    ],
                ],
                false,
            ]);
    }

    /**
     * @param array<string> $instruction
     *
     * @return void
     */
    private function givenThereIsAnInstruction($instruction)
    {
        $instructionModel = static::createMock(PaymentInstruction::class);

        $instructionModel->method('toArray')
            ->willReturn($instruction);

        $this->paymentInstructionService->method('getInstructions')
            ->willReturn($instructionModel);
    }

    /**
     * @param array<string> $instruction
     *
     * @return void
     */
    private function expectTemplateToReceiveInstruction($instruction)
    {
        $this->template->expects(static::once())
            ->method('assign')
            ->with(
                'PayPalUnifiedInvoiceInstruction',
                $instruction,
                false
            );
    }
}
