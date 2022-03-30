{block name='paypal_unified_ec_button_container_cart'}
    <div class="paypal-unified-ec--outer-button-container">
        {block name='paypal_unified_ec_button_container_cart_inner'}
            <div class="paypal-unified-ec--button-container{if $isLoginPage} left{else} right{/if}"
                 data-paypalUnifiedEcButton="true"
                 data-clientId="{$paypalUnifiedClientId}"
                 data-currency="{$paypalUnifiedCurrency}"
                 data-paypalIntent="{$paypalUnifiedIntent}"
                 data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
                 data-createOrderUrl="{url module=widgets controller=PaypalUnifiedV2ExpressCheckout action=createOrder forceSecure}"
                 data-onApproveUrl="{url module=widgets controller=PaypalUnifiedV2ExpressCheckout action=onApprove forceSecure}"
                 data-confirmUrl="{url module=frontend controller=Checkout action=confirm forceSecure}"
                 data-color="{$paypalUnifiedEcButtonStyleColor}"
                 data-shape="{$paypalUnifiedEcButtonStyleShape}"
                 data-size="{$paypalUnifiedEcButtonStyleSize}"
                 data-locale="{$paypalUnifiedButtonLocale}"
                 data-productNumber="{$sArticle.ordernumber}"
                    {if $isProduct}
                        data-buyProductDirectly="true"
                    {/if}
                 data-riskManagementMatchedProducts='{$riskManagementMatchedProducts}'
                 data-esdProducts='{$paypalUnifiedEsdProducts}'
                 data-communicationErrorMessage="{s name='error/communication' namespace='frontend/paypal_unified/checkout/messages'}{/s}"
                 data-communicationErrorTitle="{s name='error/communication/title' namespace='frontend/paypal_unified/checkout/messages'}{/s}"
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
                    window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
                });
            } else {
                window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
            }
        </script>
    {/if}
{/block}
