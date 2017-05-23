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

namespace SwagPaymentPayPalUnified\Components\Services\Installments;

use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Models\FinancingInformation;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Credit;

class OrderCreditInfoService
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $paymentId
     *
     * @return null|object
     */
    public function getCreditInfo($paymentId)
    {
        return $this->modelManager->getRepository(FinancingInformation::class)->findOneBy(['paymentId' => $paymentId]);
    }

    /**
     * @param Credit $credit
     * @param string $paymentId
     */
    public function saveCreditInfo(Credit $credit, $paymentId)
    {
        $model = new FinancingInformation();
        $model->setMonthlyPayment($credit->getMonthlyPayment()->getValue());
        $model->setTotalCost($credit->getTotalCost()->getValue());
        $model->setFeeAmount($credit->getTotalInterest()->getValue());
        $model->setTerm($credit->getTerm());
        $model->setPaymentId($paymentId);

        $this->modelManager->persist($model);
        $this->modelManager->flush();
    }
}
