{*
    This template adds the payment logo to the sidebar.

    Required parameters:
    [bool] "showPaypalLogo" - A value indicating whether or not the logo should be displayed
*}
{extends file="parent:frontend/index/sidebar.tpl"}
{block name="frontend_index_left_menu"}
    {$smarty.block.parent}
    {if $showPaypalLogo}
        {include file="frontend/paypal_unified/index/sidebar.tpl"}
    {/if}
{/block}