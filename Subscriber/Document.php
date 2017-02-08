<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Payment\Payment;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\DocumentTemplateService;
use SwagPaymentPayPalUnified\Components\Services\PaymentInstructionService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;

class Document implements SubscriberInterface
{
    /**
     * @var Payment
     */
    private $paymentMethodModel;

    /**
     * @var PaymentInstructionService
     */
    private $paymentInstructionsService;

    /**
     * @var DocumentTemplateService
     */
    private $templateService;

    /**
     * @param PaymentInstructionService $paymentInstructionService
     * @param DocumentTemplateService   $templateService
     * @param ModelManager              $modelManager
     */
    public function __construct(
        PaymentInstructionService $paymentInstructionService,
        DocumentTemplateService $templateService,
        ModelManager $modelManager
    ) {
        $paymentMethodProvider = new PaymentMethodProvider($modelManager);

        $this->paymentMethodModel = $paymentMethodProvider->getPaymentMethodModel();
        $this->paymentInstructionsService = $paymentInstructionService;
        $this->templateService = $templateService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Components_Document::assignValues::after' => 'onBeforeRenderDocument',
        ];
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function onBeforeRenderDocument(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Components_Document $document */
        $document = $args->getSubject();

        if (!$document) {
            return;
        }

        $paypalPaymentMethodId = $this->paymentMethodModel->getId();
        $orderPaymentMethodId = (int) $document->_order->payment['id'];

        //This order has not been payed with paypal unified.
        if ($paypalPaymentMethodId !== $orderPaymentMethodId) {
            return;
        }

        $paypalPaymentType = $document->_order->order->attributes['paypal_payment_type'];

        switch ($paypalPaymentType) {
            case PaymentType::PAYPAL_INVOICE:
                $this->handleInvoiceInstructions($document->_order->order['ordernumber'], $document);
                break;
            default:
                break;
        }
    }

    /**
     * @param int                           $orderNumber
     * @param \Shopware_Components_Document $document
     */
    private function handleInvoiceInstructions($orderNumber, $document)
    {
        //Collect all available containers in order to work with some of them later.
        $templateContainers = $document->_view->getTemplateVars('Containers');
        $view = $document->_view;

        //Get the new footer for the document and replace the original one
        $rawFooter = $this->templateService->getInvoiceContainer($templateContainers, $view->getTemplateVars('Order'));
        $templateContainers['PayPal_Unified_Instructions_Content']['value'] = $rawFooter['value'];

        $view->assign('Containers', $templateContainers);

        $instructions = $this->paymentInstructionsService->getInstructions($orderNumber);
        $document->_template->assign('instruction', $instructions->toArray());

        //Reassign the complete template including the new variables.
        $containerData = $view->getTemplateVars('Containers');
        $containerData['Footer'] = $containerData['PayPal_Unified_Instructions_Footer'];
        $containerData['Content_Info'] = $containerData['PayPal_Unified_Instructions_Content'];
        $containerData['Content_Info']['value'] = $document->_template->fetch('string:' . $containerData['Content_Info']['value']);
        $containerData['Content_Info']['style'] = '}' . $containerData['Content_Info']['style'] . ' #info {';

        $view->assign('Containers', $containerData);
    }
}
