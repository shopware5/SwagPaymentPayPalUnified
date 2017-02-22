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

namespace SwagPaymentPayPalUnified\Components\Services;

use SwagPaymentPayPalUnified\PayPalBundle\PaymentIntent;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\RelatedResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\ResourceType;

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
                throw new \Exception('Could not parse history from an unknown payment type');
        }
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
