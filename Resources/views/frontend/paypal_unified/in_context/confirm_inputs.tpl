{block name='frontend_checkout_confirm_paypal_unified_in_context_inputs'}
    {block name='frontend_checkout_confirm_paypal_unified_in_context_inputs_is_express_checkout'}
        <input type="hidden" value="{$paypalUnifiedInContextCheckout}" name="inContextCheckout">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_in_context_inputs_payment_id'}
        <input type="hidden" value="{$paypalUnifiedInContextOrderId}" name="paypalOrderId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_in_context_inputs_payer_id'}
        <input type="hidden" value="{$paypalUnifiedInContextPayerId}" name="payerId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_in_context_inputs_basket_id'}
        <input type="hidden" value="{$paypalUnifiedInContextBasketId}" name="basketId">
    {/block}
{/block}
