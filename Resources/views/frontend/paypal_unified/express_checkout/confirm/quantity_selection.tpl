{block name='paypal_unified_ec_checkout_confirm_quantity_selection'}
    {if !$sBasketItem.additional_details.laststock || ($sBasketItem.additional_details.laststock && $sBasketItem.additional_details.instock > 0)}
        <select name="sQuantity" disabled="disabled">
            <option value="{$sBasketItem.quantity}" selected="selected">
                {$sBasketItem.quantity}
            </option>
        </select>
    {else}
        {s name='CartColumnQuantityEmpty' namespace='frontend/checkout/cart_item'}{/s}
    {/if}
{/block}
