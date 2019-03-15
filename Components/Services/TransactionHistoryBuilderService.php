<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\PaymentIntent;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\RelatedResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\ResourceType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Sale;

class TransactionHistoryBuilderService
{
    /**
     * A helper method that parses a payment into a sales history, that can be directly used
     * in an custom model.
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getTransactionHistory(array $paymentDetails)
    {
        $payment = Payment::fromArray($paymentDetails);

        switch ($payment->getIntent()) {
            case PaymentIntent::SALE:
                return $this->getSalesHistory($payment);

            case PaymentIntent::AUTHORIZE:
            case PaymentIntent::ORDER:
                return $this->getAuthorizationHistory($payment);

            default:
                throw new \RuntimeException('Could not parse history from an unknown payment type');
        }
    }

    /**
     * @return array
     */
    public function getLegacyHistory(Sale $sale)
    {
        $amount = $sale->getAmount();
        $amountTotal = $amount->getTotal();
        $result = [
            'maxRefundableAmount' => $amountTotal,
        ];

        $result[] = [
            'id' => $sale->getId(),
            'state' => $sale->getState(),
            'amount' => $amountTotal,
            'create_time' => $sale->getCreateTime(),
            'update_time' => $sale->getUpdateTime(),
            'currency' => $amount->getCurrency(),
            'type' => $sale->getType(),
        ];

        return $result;
    }

    /**
     * A helper method that parses a payment into a sales history, that can be directly used
     * in an custom model. Additionally, this calculates and adds the maxRefundableAmount to
     * the result, which can be used as a limit for any refund in the future.
     *
     * @return array
     */
    private function getSalesHistory(Payment $payment)
    {
        $result = [];
        $maxAmount = $payment->getTransactions()->getAmount()->getTotal();

        $relatedResource = $payment->getTransactions()->getRelatedResources();

        if ($relatedResource !== null) {
            /** @var RelatedResource $sale */
            foreach ($relatedResource->getResources() as $sale) {
                $result[] = [
                    'id' => $sale->getId(),
                    'state' => $sale->getState(),
                    'amount' => $sale->getType() === ResourceType::SALE ? $sale->getAmount()->getTotal() : ($sale->getAmount()->getTotal() * -1),
                    'create_time' => $sale->getCreateTime(),
                    'update_time' => $sale->getUpdateTime(),
                    'currency' => $sale->getAmount()->getCurrency(),
                    'type' => $sale->getType(),
                ];

                if ($sale->getType() === ResourceType::REFUND) {
                    $maxAmount -= (float) $sale->getAmount()->getTotal();
                }
            }
        }

        //This is the maximum amount that can be refunded.
        $result['maxRefundableAmount'] = $maxAmount;

        return $result;
    }

    /**
     * A helper method that parses a payment into a authorization history, that can be directly used
     * in an custom model. Additionally, this calculates and adds the maxRefundableAmount to
     * the result, which can be used as a limit for any refund in the future. Furthermore, it adds the maxAuthorizableAmount
     * to the result, which indicates the maximum amount that can be authorized in the future.
     *
     * @return array
     */
    private function getAuthorizationHistory(Payment $payment)
    {
        $result = [];
        $maxRefundableAmount = 0;
        $maxAuthorizableAmount = $payment->getTransactions()->getAmount()->getTotal();

        foreach ($payment->getTransactions()->getRelatedResources()->getResources() as $authorization) {
            $amount = $authorization->getAmount();
            $amountTotal = $amount->getTotal();
            $id = $authorization->getId();
            $result[$id] = [
                'id' => $id,
                'state' => $authorization->getState(),
                'amount' => $amountTotal,
                'create_time' => $authorization->getCreateTime(),
                'update_time' => $authorization->getUpdateTime(),
                'currency' => $amount->getCurrency(),
                'type' => $authorization->getType(),
            ];

            $type = $authorization->getType();
            if ($type === ResourceType::CAPTURE) {
                $maxRefundableAmount += $amountTotal;
                $maxAuthorizableAmount -= $amountTotal;
                $result[$id]['amount'] = $amountTotal;
            } elseif ($type === ResourceType::REFUND) {
                $result[$id]['amount'] = $amountTotal * -1;
                $maxRefundableAmount -= $amountTotal;
            }
        }

        $result['maxRefundableAmount'] = $maxRefundableAmount;
        $result['maxAuthorizableAmount'] = $maxAuthorizableAmount;

        return $result;
    }
}
