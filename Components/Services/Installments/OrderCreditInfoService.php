<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return null|FinancingInformation
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
