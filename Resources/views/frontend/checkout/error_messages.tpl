{extends file='parent:frontend/checkout/error_messages.tpl'}

{block name="frontend_checkout_error_payment_blocked"}
    {$smarty.block.parent}
    {if $PayPalUnifiedPayUponInvoiceBlocked}
        {$content = "<b>{s name='error/payUponInvoiceBlocked' namespace='frontend/paypal_unified/checkout/messages'}{/s}</b><ul class='alert--list'>"}

        {foreach $payPalUnifiedPayUponInvoiceErrorList as $error}
            {if $error === '[phoneNumber]'}
                {$content = "$content<li class='list--entry'>{s name='error/payUponInvoiceBlocked/phoneNumber' namespace='frontend/paypal_unified/checkout/messages'}{/s}</li>"}
            {elseif $error === '[birthday]'}
                {$content = "$content<li class='list--entry'>{s name='error/payUponInvoiceBlocked/birthday' namespace='frontend/paypal_unified/checkout/messages'}{/s}</li>"}
            {elseif $error === '[amount]'}
                {$content = "$content<li class='list--entry'>{s name='error/payUponInvoiceBlocked/amount' namespace='frontend/paypal_unified/checkout/messages'}{/s}</li>"}
            {/if}
        {/foreach}

        {$content = "$content </ul>"}

        {include file="frontend/_includes/messages.tpl" type="warning" content="$content" icon="icon--warning"}
    {/if}
{/block}
