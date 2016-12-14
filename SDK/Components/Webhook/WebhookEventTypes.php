<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\SDK\Components\Webhook;

/**
 * @url https://developer.paypal.com/docs/integration/direct/rest/webhooks/event-names/
 */
class WebhookEventTypes
{
    /* A billing plan is created. */
    const BILLING_PLAN_CREATED = 'BILLING.PLAN.CREATED';
    /* A billing plan is updated. */
    const BILLING_PLAN_UPDATE = 'BILLING.PLAN.CREATED';
    /* A billing subscription is canceled. */
    const BILLING_SUBSCRIPTION_CANCELLED = 'BILLING.SUBSCRIPTION.CANCELLED';
    /* A billing subscription is created. */
    const BILLING_SUBSCRIPTION_CREATED = 'BILLING_SUBSCRIPTION_CREATED';
    /* A billing subscription is re-activated. */
    const BILLING_SUBSCRIPTION_REACTIVATED = 'BILLING.SUBSCRIPTION.RE-ACTIVATED';
    /* A billing subscription is suspended. */
    const BILLING_SUBSCRIPTION_SUSPENDED = 'BILLING.SUBSCRIPTION.SUSPENDED';
    /* A billing subscription is updated. */
    const BILLING_SUBSCRIPTION_UPDATED = 'BILLING.SUBSCRIPTION.UPDATED';

    /* A customer dispute is created. */
    const CUSTOMER_DISPUTE_CREATED = 'CUSTOMER.DISPUTE.CREATED';
    /* A customer dispute is resolved. */
    const CUSTOMER_DISPUTE_RESOLVED = 'CUSTOMER.DISPUTE.RESOLVED';
    /* A risk dispute is created. */
    const RISK_DISPUTE_CREATED = 'RISK.DISPUTE.CREATED';

    /* A user's consent token is revoked. */
    const IDENTITY_AUTHORIZATIONCONSENT_REVOKED = 'IDENTITY.AUTHORIZATION-CONSENT.REVOKED';
    /* An invoice is canceled. */
    const INVOICING_INVOICE_CANCELLED = 'INVOICING.INVOICE.CANCELLED';
    /* An invoice is paid. */
    const INVOICING_INVOICE_PAID = 'INVOICING.INVOICE.PAID';
    /* An invoice is refunded. */
    const INVOICING_INVOICE_REFUNDED = 'INVOICING.INVOICE.REFUNDED';

    /* A payment authorization is created, approved, executed, or a future payment authorization is created. */
    const PAYMENT_AUTHORIZATION_CREATED = 'PAYMENT.AUTHORIZATION.CREATED';
    /* A payment authorization is voided. */
    const PAYMENT_AUTHORIZATION_VOIDED = 'PAYMENT.AUTHORIZATION.CREATED';
    /* A payment capture is completed. */
    const PAYMENT_CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
    /* A payment capture is denied. */
    const PAYMENT_CAPTURE_DENIED = 'PAYMENT.CAPTURE.COMPLETED';
    /* The state of a payment capture changes to pending. */
    const PAYMENT_CAPTURE_PENDING = 'PAYMENT.CAPTURE.PENDING';
    /* Merchant refunds a payment capture. */
    const PAYMENT_CAPTURE_REFUNDED = 'PAYMENT.CAPTURE.REFUNDED';
    /* PayPal reverses a payment capture. */
    const PAYMENT_CAPTURE_REVERSED = 'PAYMENT.CAPTURE.REVERSED';

    /* A batch payout payment is denied. */
    const PAYMENT_PAYOUTSBATCH_DENIED = 'PAYMENT.PAYOUTSBATCH.DENIED';
    /* The state of a batch payout payment changes to processing. */
    const PAYMENT_PAYOUTSBATCH_PROCESSING = 'PAYMENT.PAYOUTSBATCH.PROCESSING';
    /* A batch payout payment successfully completes processing. */
    const PAYMENT_PAYOUTSBATCH_SUCCESS = 'PAYMENT.PAYOUTSBATCH.SUCCESS';
    /* A payouts item was blocked. */
    const PAYMENT_PAYOUTSITEM_BLOCKED = 'PAYMENT.PAYOUTS-ITEM.BLOCKED';
    /* A payouts item was cancelled. */
    const PAYMENT_PAYOUTSITEM_CANCELED = 'PAYMENT.PAYOUTS-ITEM.CANCELED';
    /* A payouts item was denied. */
    const PAYMENT_PAYOUTSITEM_DENIED = 'PAYMENT.PAYOUTS-ITEM.DENIED';
    /* A payouts item has failed. */
    const PAYMENT_PAYOUTSITEM_FAILED = 'PAYMENT.PAYOUTS-ITEM.FAILED';
    /* A payouts item is held. */
    const PAYMENT_PAYOUTSITEM_HELD = 'PAYMENT.PAYOUTS-ITEM.HELD';
    /* A payouts item was refunded. */
    const PAYMENT_PAYOUTSITEM_REFUNDED = 'PAYMENT.PAYOUTS-ITEM.REFUNDED';
    /* A payouts item is returned. */
    const PAYMENT_PAYOUTSITEM_RETURNED = 'PAYMENT.PAYOUTS-ITEM.RETURNED';
    /* A payouts item has succeeded. */
    const PAYMENT_PAYOUTSITEM_SUCCEEDED = 'PAYMENT.PAYOUTS-ITEM.SUCCEEDED';
    /* A payouts item is unclaimed. */
    const PAYMENT_PAYOUTSITEM_UNCLAIMED = 'PAYMENT.PAYOUTS-ITEM.UNCLAIMED';

    /* A sale is completed. */
    const PAYMENT_SALE_COMPLETED = 'PAYMENT.SALE.COMPLETED';
    /* The state of a sale changes from pending to denied. */
    const PAYMENT_SALE_DENIED = 'PAYMENT.SALE.DENIED';
    /* The state of a sale changes to pending. */
    const PAYMENT_SALE_PENDING = 'PAYMENT.SALE.PENDING';
    /* Merchant refunds the sale. */
    const PAYMENT_SALE_REFUNDED = 'PAYMENT.SALE.REFUNDED';
    /* PayPal reverses a sale. */
    const PAYMENT_SALE_REVERSED = 'PAYMENT.SALE.REVERSED';

    /* A credit card was created. */
    const VAULT_CREDITCARD_CREATED = 'VAULT.CREDIT-CARD.CREATED';
    /* A credit card was deleted. */
    const VAULT_CREDITCARD_DELETED = 'VAULT.CREDIT-CARD.DELETED';
    /* A credit card was updated. */
    const VAULT_CREDITCARD_UPDATED = 'VAULT.CREDIT-CARD.UPDATED';
}
