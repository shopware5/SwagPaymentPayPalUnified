{extends file='parent:frontend/checkout/cart.tpl'}

{* PayPal installments integration *}
{block name='frontend_checkout_cart_premium'}
    {block name='frontend_checkout_cart_premium_paypal_unified_installments'}
        {if $paypalInstallmentsMode === 'cheapest'}
            {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
        {/if}

        {if $paypalInstallmentsMode === 'simple'}
            {include file='frontend/paypal_unified/installments/upstream_presentment/cart/simple.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Express Checkout integration *}
{block name='frontend_checkout_cart_table_actions'}
    {$smarty.block.parent}

    {block name='frontend_checkout_cart_table_actions_paypal_unified_ec_button'}
        {if $paypalUnifiedEcCartActive && $paypalUnifiedUseInContext !== null}
            {include file='frontend/paypal_unified/express_checkout/button_cart.tpl'}
        {/if}
    {/block}
{/block}
