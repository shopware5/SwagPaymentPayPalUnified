{extends file='parent:frontend/checkout/cart.tpl'}

{* PayPal installments banner *}
{block name='frontend_checkout_cart_premium'}
    {block name='frontend_checkout_cart_premium_paypal_unified_installments_banner'}
        {include file='frontend/paypal_unified/installments/banner/cart.tpl'}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Express Checkout integration *}
{block name='frontend_checkout_cart_table_actions'}
    {$smarty.block.parent}

    {block name='frontend_checkout_cart_table_actions_paypal_unified_ec_button'}
        {if $paypalUnifiedEcCartActive && $paypalUnifiedUseInContext !== null && $paypalIsNotAllowed === false}
            {include file='frontend/paypal_unified/express_checkout/button_cart.tpl'}
        {/if}
    {/block}
{/block}
