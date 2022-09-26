{extends file='parent:frontend/checkout/error_messages.tpl'}

{block name='frontend_checkout_error_messages_basket_error'}
    {$smarty.block.parent}

    {if $payerActionRequired}
        {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/basket" namespace="frontend/paypal_unified/checkout/messages"}The basket has been changed during payment process. Please proceed the payment again.{/s}"}
    {/if}
{/block}
