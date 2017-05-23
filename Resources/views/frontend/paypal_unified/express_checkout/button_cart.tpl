{block name='paypal_unified_ec_button_container'}
    <div class="paypal-unified-ec--outer-button-container">
        <div class="paypal-unified-ec--button-container right"
             data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
             data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedExpressCheckout action=createPayment forceSecure}">
        </div>
    </div>
{/block}

{block name='paypal_unified_ec_button_script'}
    {if $paypalEcAjaxCart}
        <script>
            window.StateManager.addPlugin('.paypal-unified-ec--button-container', 'swagPayPalUnifiedExpressCheckoutButton');
        </script>
    {/if}
{/block}
