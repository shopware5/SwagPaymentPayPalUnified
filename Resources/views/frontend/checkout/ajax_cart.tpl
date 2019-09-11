{extends file='parent:frontend/checkout/ajax_cart.tpl'}

{* PayPal Express Checkout integration *}
{block name='frontend_checkout_ajax_cart_button_container_inner'}
    {$smarty.block.parent}
    {block name='frontend_checkout_ajax_cart_button_container_inner_paypal_unified_ec_button'}
        {foreach $sBasket.content as $product}
            {if $product.additional_details.swag_paypal_unified_express_disabled}
                {assign var="swag_paypal_unified_express_disabled" value="1"}
            {/if}
        {/foreach}
        {if $paypalUnifiedEcOffCanvasActive && $paypalUnifiedUseInContext !== null && !$swag_paypal_unified_express_disabled }
            {include file='frontend/paypal_unified/express_checkout/button_cart.tpl' paypalEcAjaxCart = true}
        {/if}
    {/block}
{/block}
