{extends file='parent:frontend/checkout/ajax_add_article.tpl'}

{* PayPal Express Checkout integration *}
{block name='checkout_ajax_add_actions_checkout'}
    {$smarty.block.parent}

    {block name='checkout_ajax_add_actions_checkout_paypal_unified_ec_button'}
        {if $paypalUnifiedEcOffCanvasActive && $paypalUnifiedUseInContext !== null}
            {include file='frontend/paypal_unified/express_checkout/button_cart.tpl' paypalEcAjaxCart=true}
        {/if}
    {/block}
{/block}
