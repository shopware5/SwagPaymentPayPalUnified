{extends file='parent:frontend/detail/buy.tpl'}

{* PayPal Installments integration *}
{block name='frontend_detail_buy_button_container'}
    {if $paypalInstallmentsMode === 'cheapest'}
        {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
    {/if}

    {if $paypalInstallmentsMode === 'simple'}
        {include file="frontend/paypal_unified/installments/upstream_presentment/detail/simple.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}
