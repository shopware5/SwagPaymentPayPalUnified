{block name='paypal_unified_ec_button_container'}
    <div class="paypal-unified-ec--outer-button-container">
        <div class="paypal-unified-ec--button-container right"
             data-expressCheckoutDetailPage="true"
             data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
             data-detailPage="true"
             data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedExpressCheckout action=createPayment forceSecure}">
        </div>
    </div>
{/block}