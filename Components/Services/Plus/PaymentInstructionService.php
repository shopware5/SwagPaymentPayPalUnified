<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\Plus;

use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Models\PaymentInstruction as PaymentInstructionModel;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;

class PaymentInstructionService
{
    const INVOICE_INSTRUCTION_DESCRIPTION = 'Pay Upon Invoice Payment Instructions';

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    public function __construct(ModelManager $modelManager, \Enlight_Event_EventManager $eventManager)
    {
        $this->modelManager = $modelManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param string $orderNumber
     *
     * @return PaymentInstructionModel|null
     */
    public function getInstructions($orderNumber)
    {
        return $this->modelManager->getRepository(PaymentInstructionModel::class)
            ->findOneBy(['orderNumber' => $orderNumber]);
    }

    /**
     * @param string $orderNumber
     */
    public function createInstructions($orderNumber, PaymentInstruction $paymentInstruction)
    {
        $model = new PaymentInstructionModel();
        $model->setOrderNumber($orderNumber);
        $model->setAccountHolder($paymentInstruction->getRecipientBanking()->getAccountHolderName());
        $model->setBankName($paymentInstruction->getRecipientBanking()->getBankName());
        $model->setBic($paymentInstruction->getRecipientBanking()->getBic());
        $model->setIban($paymentInstruction->getRecipientBanking()->getIban());
        $model->setAmount((string) $paymentInstruction->getAmount()->getValue());
        $model->setDueDate($paymentInstruction->getDueDate());
        $model->setReference($paymentInstruction->getReferenceNumber());

        $this->modelManager->persist($model);
        $this->modelManager->flush();

        $this->setInstructionToInternalComment($orderNumber, $model);

        $this->eventManager->notify(
            'SwagPaymentPayPalUnified_CreatePaymentInstructions',
            [
                'ordernumber' => $orderNumber,
                'paymentInstruction' => $paymentInstruction,
            ]
        );
    }

    /**
     * @param string $orderNumber
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
        $modelArray = ['jsonDescription' => self::INVOICE_INSTRUCTION_DESCRIPTION] + $modelArray;
        $instructionsJson = \json_encode($modelArray);

        return "\n" . $instructionsJson . "\n";
    }
}
