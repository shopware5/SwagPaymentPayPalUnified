{extends file='parent:frontend/checkout/ajax_cart.tpl'}

{* PayPal Express Checkout integration *}
{block name='frontend_checkout_ajax_cart_button_container_inner'}
    {$smarty.block.parent}

    {if $sBasket.content && !$sUserLoggedIn && $paypalUnifiedEcActive}
        {include file='frontend/paypal_unified/express_checkout/button_cart.tpl' paypalEcAjaxCart = true}
    {/if}
{/block}
