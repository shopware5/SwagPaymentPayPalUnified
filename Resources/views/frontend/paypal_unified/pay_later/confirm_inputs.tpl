{block name='frontend_checkout_confirm_paypal_unified_pay_later_inputs'}
    {block name='frontend_checkout_confirm_paypal_unified_pay_later_inputs_payment_id'}
        <input type="hidden" value="{$paypalUnifiedPayLaterOrderId}" name="paypalOrderId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_pay_later_inputs_payer_id'}
        <input type="hidden" value="{$paypalUnifiedPayLaterPayerId}" name="payerId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_pay_later_inputs_basket_id'}
        <input type="hidden" value="{$paypalUnifiedPayLaterBasketId}" name="basketId">
    {/block}

    {block name='frontend_checkout_confirm_paypal_unified_pay_later_inputs_pay_later'}
        <input type="hidden" value="{$paypalUnifiedPayLater}" name="paypalUnifiedPayLater">
    {/block}


{/block}
