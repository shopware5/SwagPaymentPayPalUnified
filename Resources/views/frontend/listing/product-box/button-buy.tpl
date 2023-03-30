{extends file='parent:frontend/listing/product-box/button-buy.tpl'}

{block name='frontend_listing_product_box_button_buy'}
    {$smarty.block.parent}

    {if $paypalUnifiedEcListingActive && $paypalIsNotAllowed === false}
        <div class="paypal-unified-ec--button-placeholder{if $paypalUnifiedEcButtonStyleSize == 'responsive'} paypal-button--is-responsive-size{/if}">
            {include file='frontend/paypal_unified/express_checkout/button.tpl' isProduct = true isListing = true}
        </div>
    {/if}
{/block}
