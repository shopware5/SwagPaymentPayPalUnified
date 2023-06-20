{extends file='parent:frontend/listing/product-box/button-detail.tpl'}

{block name='frontend_listing_product_box_button_detail_container'}
    {$smarty.block.parent}

    {if $paypalUnifiedEcListingActive && $paypalIsNotAllowed === false}
        <div class="paypal-unified-ec--button-placeholder {if $paypalUnifiedEcButtonStyleSize == 'responsive'} paypal-button--is-responsive-size{/if}"></div>
    {/if}
{/block}
