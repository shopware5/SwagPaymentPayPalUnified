{extends file='parent:frontend/checkout/cart_footer.tpl'}

{block name='frontend_checkout_cart_footer_add_voucher'}
    {if $paypalUnifiedExpressCheckout}
        <div class="paypal-unified--voucher-extension" data-paypalUnifiedEcButtonChangeCart="true">
            {$smarty.block.parent}
        </div>
    {/if}
{/block}

