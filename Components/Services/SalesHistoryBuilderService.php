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

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Sale;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Sale\SaleType;

class SalesHistoryBuilderService
{
    /**
     * A helper method that parses a payment into a sales history, that can be directly used
     * in an custom model. Additionally, this calculates and adds the maxRefundableAmount to
     * the result, which can be used as a limit for any refund in the future.
     *
     * @param array $paymentDetails
     *
     * @return array
     */
    public function getSalesHistory(array $paymentDetails)
    {
        $result = [];
        $payment = Payment::fromArray($paymentDetails);
        $maxAmount = $payment->getTransactions()->getAmount()->getTotal();

        /** @var Sale $sale */
        foreach ($payment->getTransactions()->getRelatedResources()->getSales() as $sale) {
            $result[] = [
                'id' => $sale->getId(),
                'state' => $sale->getState(),
                'amount' => $sale->getType() === SaleType::SALE ? $sale->getAmount()->getTotal() : ($sale->getAmount()->getTotal() * -1),
                'create_time' => $sale->getCreateTime(),
                'update_time' => $sale->getUpdateTime(),
                'currency' => $sale->getAmount()->getCurrency(),
            ];

            if ($sale->getType() === SaleType::REFUND) {
                $maxAmount -= (float) $sale->getAmount()->getTotal();
            }
        }

        //This is the maximum amount that can be refunded.
        $result['maxRefundableAmount'] = $maxAmount;

        return $result;
    }
}
