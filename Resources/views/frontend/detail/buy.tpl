{extends file='parent:frontend/detail/buy.tpl'}

{* PayPal Installments banner *}
{block name='frontend_detail_buy_button_container'}
    {block name='frontend_detail_buy_button_container_paypal_unified_installments_banner'}
        {include file='frontend/paypal_unified/installments/banner/product_detail.tpl'}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Express Checkout integration *}
{block name='frontend_detail_buy_button'}
    {$smarty.block.parent}

    {block name='frontend_detail_buy_button_paypal_unified_express_checkout'}
        {if !($sArticle.sConfigurator && !$activeConfiguratorSelection) && $paypalUnifiedEcDetailActive && $paypalIsNotAllowed === false}
            {include file='frontend/paypal_unified/express_checkout/button_detail.tpl'}
        {/if}
    {/block}
{/block}
