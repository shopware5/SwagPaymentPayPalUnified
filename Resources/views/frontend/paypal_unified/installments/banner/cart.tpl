{block name='paypal_unified_installments_banner_cart'}
    {if $paypalUnifiedInstallmentsBanner}
        {block name='paypal_unified_installments_banner_cart_container'}
            <div data-paypalUnifiedInstallmentsBanner="true"
                 {block name='paypal_unified_installments_banner_data_attributes'}
                 data-ratio="20x1"
                 data-color="gray"
                 data-amount="{$paypalUnifiedInstallmentsBannerAmount}"
                 data-currency="{$paypalUnifiedInstallmentsBannerCurrency}"
                 {/block}
                 class="paypal-unified-installments-banner--cart">
            </div>
        {/block}
    {/if}
{/block}
