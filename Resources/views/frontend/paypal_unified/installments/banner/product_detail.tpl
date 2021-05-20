{block name='paypal_unified_installments_banner_product_detail'}
    {if $paypalUnifiedInstallmentsBanner && $paypalIsNotAllowed === false}
        {block name='paypal_unified_installments_banner_product_detail_container'}
            <div data-paypalUnifiedInstallmentsBanner="true"
                 {block name='paypal_unified_installments_banner_data_attributes'}
                 data-amount="{$paypalUnifiedInstallmentsBannerAmount}"
                 data-currency="{$paypalUnifiedInstallmentsBannerCurrency}"
                 data-buyerCountry="{$paypalUnifiedInstallmentsBannerBuyerCountry}"
                 {/block}
                 class="paypal-unified-installments-banner--product-detail">
            </div>
        {/block}
    {/if}
{/block}
