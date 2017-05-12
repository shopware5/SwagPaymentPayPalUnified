{extends file="parent:frontend/checkout/cart.tpl"}

{* PayPal installments integration *}
{block name='frontend_checkout_cart_premium'}
    {if $paypalInstallmentsMode === 'cheapest'}
        {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
    {/if}

    {if $paypalInstallmentsMode === 'simple'}
        {include file="frontend/paypal_unified/installments/upstream_presentment/cart/simple.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}