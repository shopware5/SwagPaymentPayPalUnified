{extends file='parent:frontend/listing/product-box/button-detail.tpl'}

{block name='frontend_listing_product_box_button_detail_container'}
    {$smarty.block.parent}

    {if $paypalUnifiedEcListingActive}
        <div class="paypal-unified-ec--button-placeholder"></div>
    {/if}
{/block}
