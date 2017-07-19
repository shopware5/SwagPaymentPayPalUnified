{block name='paypal_unified_ec_button_container_cart'}
    <div class="paypal-unified-ec--outer-button-container">
        {block name='paypal_unified_ec_button_container_cart_inner'}
            <div class="paypal-unified-ec--button-container right"
                {if $paypalUnifiedUseInContext}
                 data-paypalUnifiedEcButtonInContext="true"
                {else}
                 data-paypalUnifiedEcButton="true"
                {/if}
                 data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
                 data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedExpressCheckout action=createPayment forceSecure}"
                 data-color="{$paypalUnifiedEcButtonStyleColor}"
                 data-shape="{$paypalUnifiedEcButtonStyleShape}"
                 data-size="{$paypalUnifiedEcButtonStyleSize}"
                 data-cart="true"
                {block name='paypal_unified_ec_button_container_cart_data'}{/block}>
            </div>
        {/block}
    </div>
{/block}

{block name='paypal_unified_ec_button_script_cart'}
    {if $paypalEcAjaxCart}
        <script>
            {* Shopware 5.3 may load the javaScript asynchronously, therefore
               we have to use the asyncReady function *}
            var asyncConf = ~~("{$theme.asyncJavascriptLoading}");
            if (typeof document.asyncReady === 'function' && asyncConf) {
                document.asyncReady(function() {
                    {if $paypalUnifiedUseInContext}
                        window.StateManager.addPlugin('*[data-paypalUnifiedEcButtonInContext="true"]*[data-cart="true"]', 'swagPayPalUnifiedExpressCheckoutButtonInContext');
                    {else}
                        window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]*[data-cart="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
                    {/if}

                });
            } else {
                {if $paypalUnifiedUseInContext}
                    window.StateManager.addPlugin('*[data-paypalUnifiedEcButtonInContext="true"]*[data-cart="true"]', 'swagPayPalUnifiedExpressCheckoutButtonInContext');
                {else}
                    window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]*[data-cart="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
                {/if}
            }
        </script>
    {/if}
{/block}
