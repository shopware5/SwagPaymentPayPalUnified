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

{* PayPal Express Checkout integration *}
{block name="frontend_detail_buy_button"}
    {$smarty.block.parent}

    {if !($sArticle.sConfigurator && !$activeConfiguratorSelection) && $paypalExpressCheckoutDetailActive}
        {include file='frontend/paypal_unified/express_checkout/button_detail.tpl'}
    {/if}
{/block}
