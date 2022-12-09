{block name='frontend_checkout_confirm_paypal_unified_ec_inputs'}
    {block name='frontend_checkout_confirm_paypal_unified_ec_inputs_is_express_checkout'}
        <input type="hidden" value="{$paypalUnifiedExpressCheckout}" name="expressCheckout">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_ec_inputs_payment_id'}
        <input type="hidden" value="{$paypalUnifiedExpressOrderId}" name="token">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_ec_inputs_payer_id'}
        <input type="hidden" value="{$paypalUnifiedExpressPayerId}" name="payerId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_ec_inputs_basket_id'}
        <input type="hidden" value="{$paypalUnifiedExpressBasketId}" name="basketId">
    {/block}
{/block}
