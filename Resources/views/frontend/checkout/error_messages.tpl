{extends file='parent:frontend/checkout/error_messages.tpl'}

{block name="frontend_checkout_error_payment_blocked"}
    {$smarty.block.parent}
    {if $PayPalUnifiedPayUponInvoiceBlocked}
        {include file="frontend/_includes/messages.tpl" type="warning" content="{s name='error/payUponInvoiceBlocked' namespace='frontend/paypal_unified/checkout/messages'}{/s}" icon="icon--warning"}
    {/if}
{/block}
