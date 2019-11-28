{block name='paypal_unified_installments_banner_product_detail'}
    {if $paypalUnifiedInstallmentsBanner}
        {block name='paypal_unified_installments_banner_product_detail_container'}
            <div data-paypalUnifiedInstallmentsBanner="true"
                 {block name='paypal_unified_installments_banner_data_attributes'}
                 data-amount="{$paypalUnifiedInstallmentsBannerAmount}"
                 data-currency="{$paypalUnifiedInstallmentsBannerCurrency}"
                 {/block}
                 class="paypal-unified-installments-banner--product-detail">
            </div>
        {/block}
    {/if}
{/block}
