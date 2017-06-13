{block name='paypal_unified_ec_button_container'}
    <div class="paypal-unified-ec--outer-button-container">
        <div class="paypal-unified-ec--button-container right"
            {if $paypalUnifiedUseInContext}
             data-ecButtonInContext="true"
            {else}
             data-ecButton="true"
            {/if}
             data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
             data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedExpressCheckout action=createPayment forceSecure}">
        </div>
    </div>
{/block}

{block name='paypal_unified_ec_button_script'}
    {if $paypalEcAjaxCart}
        <script>
            {* Shopware 5.3 may load the javaScript asynchronously, therefore
               we have to use the asyncReady function *}
            var asyncConf = ~~("{$theme.asyncJavascriptLoading}");
            if (typeof document.asyncReady === 'function' && asyncConf) {
                document.asyncReady(function() {
                    window.StateManager.addPlugin('.paypal-unified-ec--button-container', 'swagPayPalUnifiedExpressCheckoutButton');
                });
            } else {
                window.StateManager.addPlugin('.paypal-unified-ec--button-container', 'swagPayPalUnifiedExpressCheckoutButton');
            }
        </script>
    {/if}
{/block}
