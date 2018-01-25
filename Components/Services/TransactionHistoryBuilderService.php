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
     * @param array $paymentDetails
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
     * @param Sale $sale
     *
     * @return array
     */
    public function getLegacyHistory(Sale $sale)
    {
        $result = [
            'maxRefundableAmount' => $sale->getAmount()->getTotal(),
        ];

        $result[] = [
            'id' => $sale->getId(),
            'state' => $sale->getState(),
            'amount' => $sale->getAmount()->getTotal(),
            'create_time' => $sale->getCreateTime(),
            'update_time' => $sale->getUpdateTime(),
            'currency' => $sale->getAmount()->getCurrency(),
            'type' => $sale->getType(),
        ];

        return $result;
    }

    /**
     * A helper method that parses a payment into a sales history, that can be directly used
     * in an custom model. Additionally, this calculates and adds the maxRefundableAmount to
     * the result, which can be used as a limit for any refund in the future.
     *
     * @param Payment $payment
     *
     * @return array
     */
    private function getSalesHistory(Payment $payment)
    {
        $result = [];
        $maxAmount = $payment->getTransactions()->getAmount()->getTotal();

        /** @var RelatedResource $sale */
        foreach ($payment->getTransactions()->getRelatedResources()->getResources() as $sale) {
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
     * @param Payment $payment
     *
     * @return array
     */
    private function getAuthorizationHistory(Payment $payment)
    {
        $result = [];
        $maxRefundableAmount = 0;
        $maxAuthorizableAmount = $payment->getTransactions()->getAmount()->getTotal();

        foreach ($payment->getTransactions()->getRelatedResources()->getResources() as $authorization) {
            $id = $authorization->getId();
            $result[$id] = [
                'id' => $id,
                'state' => $authorization->getState(),
                'amount' => $authorization->getAmount()->getTotal(),
                'create_time' => $authorization->getCreateTime(),
                'update_time' => $authorization->getUpdateTime(),
                'currency' => $authorization->getAmount()->getCurrency(),
                'type' => $authorization->getType(),
            ];

            $type = $authorization->getType();
            if ($type === ResourceType::CAPTURE) {
                $maxRefundableAmount += $authorization->getAmount()->getTotal();
                $maxAuthorizableAmount -= $authorization->getAmount()->getTotal();
                $result[$id]['amount'] = $authorization->getAmount()->getTotal();
            } elseif ($type === ResourceType::REFUND) {
                $result[$id]['amount'] = $authorization->getAmount()->getTotal() * -1;
                $maxRefundableAmount -= $authorization->getAmount()->getTotal();
            }
        }

        $result['maxRefundableAmount'] = $maxRefundableAmount;
        $result['maxAuthorizableAmount'] = $maxAuthorizableAmount;

        return $result;
    }
}
