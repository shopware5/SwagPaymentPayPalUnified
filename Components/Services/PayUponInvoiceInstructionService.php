<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use Enlight_Event_EventManager as EventManager;
use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Models\PaymentInstruction as PaymentInstructionModel;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice\DepositBankDetails;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Payments\Capture\Amount;

class PayUponInvoiceInstructionService
{
    const INSTRUCTION_DESCRIPTION = 'Pay Upon Invoice Payment Instructions';

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var OrderPropertyHelper
     */
    private $orderPropertyHelper;

    public function __construct(ModelManager $modelManager, EventManager $eventManager, OrderPropertyHelper $orderPropertyHelper)
    {
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
        $this->orderPropertyHelper = $orderPropertyHelper;
    }

    /**
     * @param string $orderNumber
     *
     * @return void
     */
    public function createInstructions($orderNumber, Order $order)
    {
        $bankDetails = $this->orderPropertyHelper->getBankDetails($order);
        if (!$bankDetails instanceof DepositBankDetails) {
            return;
        }

        $capture = $this->orderPropertyHelper->getFirstCapture($order);
        if (!$capture instanceof Capture) {
            return;
        }

        $amount = $capture->getAmount();
        if (!$amount instanceof Amount) {
            return;
        }

        $model = new PaymentInstructionModel();
        $model->setOrderNumber($orderNumber);
        $model->setAccountHolder($bankDetails->getAccountHolderName());
        $model->setBankName($bankDetails->getBankName());
        $model->setBic($bankDetails->getBic());
        $model->setIban($bankDetails->getIban());
        $model->setAmount($amount->getValue());
        $model->setReference($order->getId());

        $this->modelManager->persist($model);
        $this->modelManager->flush();

        $this->setInstructionToInternalComment($orderNumber, $model);

        $this->eventManager->notify(
            'SwagPaymentPayPalUnified_PayUponInvoice_CreatePaymentInstructions',
            [
                'ordernumber' => $orderNumber,
                'paymentInstruction' => $model,
            ]
        );
    }

    /**
     * @param string $orderNumber
     *
     * @return void
     */
    private function setInstructionToInternalComment($orderNumber, PaymentInstructionModel $model)
    {
        $connection = $this->modelManager->getConnection();
        $instructionsString = $this->getInstructionString($model);

        $query = $connection->createQueryBuilder();
        $query->update('s_order')
            ->set('internalcomment', 'CONCAT(internalcomment, :instructionsString) ')
            ->where('ordernumber = :orderNumber')
            ->setParameters([
                'instructionsString' => $instructionsString,
                'orderNumber' => $orderNumber,
            ]);
        $query->execute();
    }

    /**
     * @return string
     */
    private function getInstructionString(PaymentInstructionModel $model)
    {
        $modelArray = $model->toArray();
        unset($modelArray['id'], $modelArray['order']);
        $modelArray = ['jsonDescription' => self::INSTRUCTION_DESCRIPTION] + $modelArray;
        $instructionsJson = \json_encode($modelArray);

        return "\n" . $instructionsJson . "\n";
    }
}
