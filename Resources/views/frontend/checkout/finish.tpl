{extends file="parent:frontend/checkout/finish.tpl"}

{* PayPal Plus integration *}
{block name='frontend_checkout_finish_teaser'}
    {$smarty.block.parent}

    {if $paypalUnifiedPaymentInstructions}
        {include file='frontend/paypal_unified/plus/checkout/payment_instructions.tpl'}
    {/if}
{/block}