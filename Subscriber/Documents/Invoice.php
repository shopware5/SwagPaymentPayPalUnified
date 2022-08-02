<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber\Documents;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs as EventArgs;
use Enlight_Hook_HookArgs as HookArgs;
use Enlight_Template_Manager as Template;
use Shopware_Components_Document as Document;
use Shopware_Components_Snippet_Manager as SnippetManager;
use Shopware_Components_Translation;
use SwagPaymentPayPalUnified\Components\Document\InvoiceDocumentHandler;
use SwagPaymentPayPalUnified\Components\Document\PuiInvoiceDocumentHandler;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\Plus\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class Invoice implements SubscriberInterface
{
    /**
     * @var PaymentInstructionService
     */
    private $paymentInstructionsService;

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var SnippetManager
     */
    private $snippetManager;

    /**
     * @var Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var Template
     */
    private $templateManager;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    public function __construct(
        PaymentInstructionService $paymentInstructionService,
        Connection $dbalConnection,
        SnippetManager $snippetManager,
        Shopware_Components_Translation $translation = null,
        Template $templateManager,
        PaymentMethodProviderInterface $paymentMethodProvider,
        SettingsServiceInterface $settingsService
    ) {
        $this->paymentInstructionsService = $paymentInstructionService;
        $this->dbalConnection = $dbalConnection;
        $this->snippetManager = $snippetManager;
        $this->translation = $translation;
        $this->templateManager = $templateManager;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->settingsService = $settingsService;

        if ($this->translation === null) {
            $this->translation = new Shopware_Components_Translation();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Components_Document::assignValues::after' => 'onBeforeRenderDocument',
            'Shopware_Modules_Order_SendMail_FilterVariables' => 'onFilterMailVariables',
        ];
    }

    /**
     * @return void
     */
    public function onBeforeRenderDocument(HookArgs $args)
    {
        /** @var Document|null $document */
        $document = $args->getSubject();

        if (!$document) {
            return;
        }

        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME);
        $payUponInvoiceId = $this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME);
        $orderNumber = $document->_order->order['ordernumber'];
        $orderPaymentMethodId = (int) $document->_order->payment['id'];

        if ($orderPaymentMethodId === $payUponInvoiceId) {
            (new PuiInvoiceDocumentHandler(
                $this->snippetManager,
                $this->dbalConnection,
                $this->paymentInstructionsService,
                $this->translation
            ))->handleDocument($orderNumber, $document);

            return;
        }
        // This order has not been payed with paypal unified.
        if ($orderPaymentMethodId !== $unifiedPaymentId) {
            return;
        }

        $paypalPaymentType = $document->_order->order->attributes['swag_paypal_unified_payment_type'];

        if ($paypalPaymentType !== PaymentType::PAYPAL_INVOICE) {
            return;
        }

        $documentHandler = new InvoiceDocumentHandler(
            $this->paymentInstructionsService,
            $this->dbalConnection,
            $this->snippetManager,
            $this->translation
        );
        $documentHandler->handleDocument($orderNumber, $document);
    }

    /**
     * @return array<string,mixed>
     */
    public function onFilterMailVariables(EventArgs $eventArgs)
    {
        $vars = $eventArgs->getReturn();

        $paymentMethodName = $vars['additional']['payment']['name'];

        if ($paymentMethodName === PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME) {
            return $this->addRatePayLegalText($vars);
        }

        if ($paymentMethodName !== PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME) {
            return $vars;
        }

        $vars['additional']['payment']['additionaldescription'] = $this->templateManager->fetch(
            \sprintf('string:%s', $vars['additional']['payment']['additionaldescription'])
        );

        return $vars;
    }

    /**
     * @param array<string,mixed> $variables
     *
     * @return array<string,mixed>
     */
    private function addRatePayLegalText(array $variables)
    {
        $showRatePayHint = (bool) $this->settingsService->get(SettingsServiceInterface::SETTING_PUI_SHOW_RATEPAY_HINT, SettingsTable::PAY_UPON_INVOICE);

        $ratePayLegalText = $this->templateManager->fetch(
            \sprintf('string:%s', $this->snippetManager->getNamespace('document/rate_pay')->get('hint'))
        );

        $variables['additional']['paypalUnifiedRatePayHint'] = $ratePayLegalText;

        if (!$showRatePayHint) {
            return $variables;
        }

        $variables['additional']['payment']['additionaldescription'] = $ratePayLegalText;

        return $variables;
    }
}
