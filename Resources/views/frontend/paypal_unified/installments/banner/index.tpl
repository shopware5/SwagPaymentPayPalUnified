{block name='paypal_unified_installments_banner_index'}
    {if $paypalUnifiedInstallmentsBanner && $paypalUnifiedInstallmentsBannerClientId}
        {block name='paypal_unified_installments_banner_index_script'}
            <script src="https://www.paypal.com/sdk/js?client-id={$paypalUnifiedInstallmentsBannerClientId}&components=messages"
                    data-namespace="payPalInstallmentsBannerJS">
            </script>
        {/block}
    {/if}
{/block}
