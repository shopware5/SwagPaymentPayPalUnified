{namespace name="frontend/paypal_unified/detail/buy"}

{block name="frontend_detail_buy_paypal_unified_installments_simple"}
    {if $paypalInstallmentsMode === "cheapest"}
        {include file="frontend/paypal_unified/installments/_includes/ajax_loading.tpl"}
    {elseif $paypalInstallmentsMode === "simple"}
        {include file="frontend/paypal_unified/installments/upstream_presentment/simple.tpl"}
    {/if}
{/block}
