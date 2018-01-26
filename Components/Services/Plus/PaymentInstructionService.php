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
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * PaymentInstructionService constructor.
     *
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $orderNumber
     *
     * @return null|PaymentInstructionModel
     */
    public function getInstructions($orderNumber)
    {
        /** @var PaymentInstructionModel $instructionModel */
        $instructionModel = $this->modelManager->getRepository(PaymentInstructionModel::class)
            ->findOneBy(['orderNumber' => $orderNumber]);

        return $instructionModel;
    }

    /**
     * @param string             $orderNumber
     * @param PaymentInstruction $paymentInstruction
     */
    public function createInstructions($orderNumber, PaymentInstruction $paymentInstruction)
    {
        $model = new PaymentInstructionModel();
        $model->setOrderNumber($orderNumber);
        $model->setAccountHolder($paymentInstruction->getRecipientBanking()->getAccountHolderName());
        $model->setBankName($paymentInstruction->getRecipientBanking()->getBankName());
        $model->setBic($paymentInstruction->getRecipientBanking()->getBic());
        $model->setIban($paymentInstruction->getRecipientBanking()->getIban());
        $model->setAmount($paymentInstruction->getAmount()->getValue());
        $model->setDueDate($paymentInstruction->getDueDate());
        $model->setReference($paymentInstruction->getReferenceNumber());

        $this->modelManager->persist($model);
        $this->modelManager->flush();

        $this->setInstructionToInternalComment($orderNumber, $model);
    }

    /**
     * @param string                  $orderNumber
     * @param PaymentInstructionModel $model
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
     * @param PaymentInstructionModel $model
     *
     * @return string
     */
    private function getInstructionString(PaymentInstructionModel $model)
    {
        $modelArray = $model->toArray();
        unset($modelArray['id'], $modelArray['order']);
        $modelArray = ['jsonDescription' => 'Pay Upon Invoice Payment Instructions'] + $modelArray;
        $instructionsJson = json_encode($modelArray);

        return "\n" . $instructionsJson . "\n";
    }
}
