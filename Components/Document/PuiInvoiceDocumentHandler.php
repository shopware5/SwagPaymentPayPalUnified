<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Document;

use Doctrine\DBAL\Connection;
use Enlight_Template_Manager as TemplateManager;
use PDO;
use Shopware_Components_Document as Document;
use Shopware_Components_Snippet_Manager as SnippetManager;
use Shopware_Components_Translation as Translator;
use Smarty_Data as View;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;

class PuiInvoiceDocumentHandler
{
    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var PaymentInstructionService
     */
    private $instructionService;

    /**
     * @var Translator
     */
    private $translation;

    public function __construct(
        SnippetManager $snippetManager,
        Connection $connection,
        PaymentInstructionService $instructionService,
        Translator $translation
    ) {
        $this->snippetManager = $snippetManager;
        $this->connection = $connection;
        $this->instructionService = $instructionService;
        $this->translation = $translation;
    }

    /**
     * @param string $orderNumber
     *
     * @return void
     */
    public function handleDocument($orderNumber, Document $document)
    {
        $rawTemplate = $this->getRawTemplate($document->_typID);
        if (empty($rawTemplate)) {
            return;
        }

        $view = $document->_view;
        $template = $document->_template;

        $rawTemplate = $this->translateFooter($view, $rawTemplate);

        $this->assignDataToDocumentTemplate($template, $orderNumber);

        $view->assign('Containers', $this->prepareContainers($view, $rawTemplate, $template));
    }

    /**
     * @return string
     */
    private function getNotification(TemplateManager $templateManager)
    {
        return $this->renderTemplateString(
            $templateManager,
            $this->snippetManager->getNamespace('document/rate_pay')->get('hint')
        );
    }

    /**
     * @param string $templateString
     *
     * @return string
     */
    private function renderTemplateString(TemplateManager $templateManager, $templateString)
    {
        return $templateManager->fetch($this->prepareTemplateString($templateString));
    }

    /**
     * @param string $templateString
     *
     * @return string
     */
    private function prepareTemplateString($templateString)
    {
        return \sprintf('string:%s', $templateString);
    }

    /**
     * @param int $documentId
     *
     * @return array<string,mixed>
     */
    private function getRawTemplate($documentId)
    {
        $templates = $this->connection->createQueryBuilder()
            ->select(['name', 'tpl.*'])
            ->from('s_core_documents_box', 'tpl')
            ->where('documentID = :documentId')
            ->andWhere('name IN (:names)')
            ->setParameter('documentId', $documentId)
            ->setParameter('names', ['PayPal_Unified_Ratepay_Instructions', 'PayPal_Unified_Instructions_Footer'], Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        if (!\is_array($templates['PayPal_Unified_Ratepay_Instructions'])) {
            return [];
        }

        foreach ($templates as $templateName => $template) {
            $templates[$templateName] = $template[0];
        }

        return $templates;
    }

    /**
     * @param string $orderNumber
     *
     * @return void
     */
    private function assignDataToDocumentTemplate(TemplateManager $templateManager, $orderNumber)
    {
        $notification = $this->getNotification($templateManager);
        if (!empty($notification)) {
            $templateManager->assign('payUponInvoiceRatepayInstructions', $notification);
        }

        $instructions = $this->instructionService->getInstructions($orderNumber);
        if ($instructions) {
            $templateManager->assign('PayPalUnifiedInvoiceInstruction', $instructions->toArray());
        }
    }

    /**
     * @param array<string,mixed> $rawTemplate
     *
     * @return array<string,mixed>
     */
    private function prepareContainers(View $view, array $rawTemplate, TemplateManager $template)
    {
        $templateContainers = $view->getTemplateVars('Containers');
        $templateContainers['Footer'] = $rawTemplate['PayPal_Unified_Instructions_Footer'];
        $templateContainers['Content_Info'] = $rawTemplate['PayPal_Unified_Ratepay_Instructions'];
        $templateContainers['Content_Info']['value'] = $this->renderTemplateString($template, $rawTemplate['PayPal_Unified_Ratepay_Instructions']['value']);
        $templateContainers['Content_Info']['style'] = '}' . $rawTemplate['PayPal_Unified_Ratepay_Instructions']['style'] . ' #info {';

        return $templateContainers;
    }

    /**
     * @param array<string,mixed> $rawTemplate
     *
     * @return array<string,mixed>
     */
    private function translateFooter(View $view, array $rawTemplate)
    {
        $orderData = $view->getTemplateVars('Order');

        $translation = $this->translation->read(
            $orderData['_order']['language'],
            'documents',
            $rawTemplate['PayPal_Unified_Ratepay_Instructions']['documentID']
        );

        if (!empty($translation[1]['PayPal_Unified_Ratepay_Instructions'])) {
            $rawTemplate['PayPal_Unified_Ratepay_Instructions']['value'] = $translation[1]['PayPal_Unified_Ratepay_Instructions'];
        }

        if (!empty($translation[1]['PayPal_Unified_Ratepay_Instructions_Style'])) {
            $rawTemplate['PayPal_Unified_Ratepay_Instructions']['style'] = $translation[1]['PayPal_Unified_Ratepay_Instructions_Style'];
        }

        if (!empty($translation[1]['PayPal_Unified_Instructions_Footer_Value'])) {
            $rawTemplate['PayPal_Unified_Instructions_Footer']['value'] = $translation[1]['PayPal_Unified_Instructions_Footer_Value'];
        }

        if (!empty($translation[1]['PayPal_Unified_Instructions_Footer_Style'])) {
            $rawTemplate['PayPal_Unified_Instructions_Footer']['style'] = $translation[1]['PayPal_Unified_Instructions_Footer_Style'];
        }

        return $rawTemplate;
    }
}
