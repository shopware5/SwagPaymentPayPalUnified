{block name='paypal_unified_installments_banner_ajax_cart'}
    {if $paypalUnifiedInstallmentsBanner && $paypalIsNotAllowed === false}
        {block name='paypal_unified_installments_banner_ajax_cart_container'}
            <div data-paypalUnifiedInstallmentsBanner="true"
                 {block name='paypal_unified_installments_banner_data_attributes'}
                 data-color="gray"
                 data-amount="{$paypalUnifiedInstallmentsBannerAmount}"
                 data-currency="{$paypalUnifiedInstallmentsBannerCurrency}"
                 {/block}
                 class="paypal-unified-installments-banner--cart">
            </div>
        {/block}

        {block name='paypal_unified_installments_banner_ajax_cart_script'}
            <script>
                {* Shopware 5.3 may load the javaScript asynchronously, therefore
                   we have to use the asyncReady function *}
                var asyncConf = ~~("{$theme.asyncJavascriptLoading}");
                if (typeof document.asyncReady === 'function' && asyncConf) {
                    document.asyncReady(function() {
                        window.StateManager.addPlugin('*[data-paypalUnifiedInstallmentsBanner="true"]', 'swagPayPalUnifiedInstallmentsBanner');
                    });
                } else {
                    window.StateManager.addPlugin('*[data-paypalUnifiedInstallmentsBanner="true"]', 'swagPayPalUnifiedInstallmentsBanner');
                }
            </script>
        {/block}
    {/if}
{/block}
