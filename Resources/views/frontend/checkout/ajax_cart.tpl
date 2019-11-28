{extends file='parent:frontend/checkout/ajax_cart.tpl'}

{* PayPal Express Checkout integration *}
{block name='frontend_checkout_ajax_cart_button_container_inner'}
    {$smarty.block.parent}

    {block name='frontend_checkout_ajax_cart_button_container_inner_paypal_unified_ec_button'}
        {if $paypalUnifiedEcOffCanvasActive && $paypalUnifiedUseInContext !== null}
            {include file='frontend/paypal_unified/express_checkout/button_cart.tpl' paypalEcAjaxCart = true}
        {/if}
    {/block}

    {block name='frontend_checkout_ajax_cart_button_container_inner_paypal_unified_installments_banner'}
        {include file='frontend/paypal_unified/installments/banner/ajax_cart.tpl'}
    {/block}
{/block}
