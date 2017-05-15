{extends file="parent:frontend/checkout/confirm_header.tpl"}

{block name="frontend_checkout_cart_header_tax"}
    {if $paypalInstallmentsMode === 'selected'}
        {block name="frontend_paypal_unified_installments_cart_header_tax"}
            <div class="panel--th column--tax-price block is--align-right"></div>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_cart_header_actions"}
    {if $paypalInstallmentsMode !== 'selected'}
        {$smarty.block.parent}
    {/if}
{/block}