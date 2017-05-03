{block name="frontend_paypal_unified_installments_upstream_presentment"}
    {if $paypalInstallmentsPageType === "detail"}
        {include file="frontend/paypal_unified/installments/upstream_presentment/detail/simple.tpl"}
    {elseif $paypalInstallmentsPageType === "cart"}
        {include file="frontend/paypal_unified/installments/upstream_presentment/cart/simple.tpl"}
    {/if}
{/block}
