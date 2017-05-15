{extends file="parent:frontend/checkout/items/product.tpl"}

{block name="frontend_checkout_cart_item_quantity_selection"}
    {if $paypalInstallmentsMode === 'selected'}
        {block name="frontend_paypal_unified_installments_cart_product_quantity"}
            {$sBasketItem.quantity}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_cart_item_delete_article"}
    {if $paypalInstallmentsMode !== 'selected'}
        {$smarty.block.parent}
    {/if}
{/block}