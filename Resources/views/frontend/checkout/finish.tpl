{extends file='parent:frontend/checkout/finish.tpl'}

{block name='frontend_checkout_finish_teaser'}
    {block name='frontend_checkout_finish_teaser_error_messages_paypal_unified_errors'}
        {if $paypalUnifiedErrorCode}
            {include file='frontend/paypal_unified/checkout/error_message.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}

    {* PayPal Plus integration *}
    {block name='frontend_checkout_finish_teaser_paypal_unified_plus'}
        {if $paypalUnifiedPaymentInstructions}
            {include file='frontend/paypal_unified/plus/checkout/payment_instructions.tpl'}
        {/if}
    {/block}
{/block}
