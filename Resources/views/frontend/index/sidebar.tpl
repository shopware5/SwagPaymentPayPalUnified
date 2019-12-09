{extends file='parent:frontend/index/sidebar.tpl'}

{block name='frontend_index_left_menu'}
    {$smarty.block.parent}

    {block name='frontend_index_left_menu_paypal_unified_logos'}
        {if $paypalUnifiedShowLogo || $paypalUnifiedShowInstallmentsLogo || $paypalUnifiedAdvertiseReturns || $paypalUnifiedInstallmentsBanner}
            {include file='frontend/paypal_unified/index/sidebar.tpl'}
        {/if}
    {/block}
{/block}
