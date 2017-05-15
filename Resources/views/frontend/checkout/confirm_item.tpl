{extends file="parent:frontend/checkout/confirm_item.tpl"}

{block name="frontend_checkout_cart_item_tax_price"}
    {if $paypalInstallmentsMode === 'selected'}
        {block name="frontend_paypal_unified_installments_cart_item_tax"}
            <div class="panel--td column--tax-price block is--align-right"></div>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}