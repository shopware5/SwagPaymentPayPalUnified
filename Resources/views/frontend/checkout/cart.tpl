{extends file="parent:frontend/checkout/cart.tpl"}

{block name='frontend_checkout_cart_premium'}
    {include file="frontend/paypal_unified/installments/upstream_presentment.tpl"}

    {$smarty.block.parent}
{/block}