{block name='frontend_checkout_confirm_paypal_unified_ec_inputs'}
    {block name='frontend_checkout_confirm_paypal_unified_spb_inputs_is_spb_checkout'}
        <input type="hidden" value="{$paypalUnifiedSpbCheckout}" name="spbCheckout">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_spb_inputs_payment_id'}
        <input type="hidden" value="{$paypalUnifiedSpbPaymentId}" name="paymentId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_spb_inputs_payer_id'}
        <input type="hidden" value="{$paypalUnifiedSpbPayerId}" name="payerId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_spb_inputs_basket_id'}
        <input type="hidden" value="{$paypalUnifiedSpbBasketId}" name="basketId">
    {/block}
{/block}
