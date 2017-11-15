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
