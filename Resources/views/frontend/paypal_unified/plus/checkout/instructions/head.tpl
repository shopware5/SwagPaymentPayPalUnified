{namespace name='frontend/paypal_unified/checkout/finish'}
{block name='frontend_checkout_finish_swag_payment_paypal_unified_head'}
{/block}

{block name='frontend_checkout_finish_swag_payment_paypal_unified_instruction'}
    <div class="unified--instruction">
        {s name='instructions/pleaseTransfer'}Please transfer{/s} {$paypalUnifiedPaymentInstructions.amount|currency}
        {s name='instructions/until'}until{/s} {$paypalUnifiedPaymentInstructions.dueDate|date_format: "%d.%m.%Y"}.
    </div>
{/block}
