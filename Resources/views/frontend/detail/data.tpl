{extends file='parent:frontend/detail/data.tpl'}

{block name='frontend_widgets_delivery_infos'}
    {$smarty.block.parent}

    {if $payPalUnifiedInstallmentsNotAvailable}
        {include file='frontend/paypal_unified/installments/financing_details/not_available.tpl'}
    {/if}
{/block}
