<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class StateReasonCode
{
    const CHARGEBACK = 'CHARGEBACK';
    const GUARANTEE = 'GUARANTEE';
    const BUYER_COMPLAINT = 'BUYER_COMPLAINT';
    const REFUND = 'REFUND';
    const UNCONFIRMED_SHIPPING_ADDRESS = 'UNCONFIRMED_SHIPPING_ADDRESS';
    const ECHECK = 'ECHECK';
    const INTERNATIONAL_WITHDRAWAL = 'INTERNATIONAL_WITHDRAWAL';
    const RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION = 'RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION';
    const PAYMENT_REVIEW = 'PAYMENT_REVIEW';
    const REGULATORY_REVIEW = 'REGULATORY_REVIEW';
    const UNILATERAL = 'UNILATERAL';
    const VERIFICATION_REQUIRED = 'VERIFICATION_REQUIRED';
    const TRANSACTION_APPROVED_AWAITING_FUNDING = 'TRANSACTION_APPROVED_AWAITING_FUNDING';
}
