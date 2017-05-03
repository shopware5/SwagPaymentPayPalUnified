{extends file='parent:frontend/detail/buy.tpl'}

{block name='frontend_detail_buy_button_container'}
    {if $paypalInstallmentsMode !== 'none'}
        {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
    {/if}

    {$smarty.block.parent}
{/block}
