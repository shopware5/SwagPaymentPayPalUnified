{block name='paypal_unified_ec_button_container'}
    <div class="paypal-unified-ec--outer-button-container">
        <div class="paypal-unified-ec--button-container right"
            {if $paypalUnifiedUseInContext}
             data-ecButtonInContext="true"
            {else}
             data-ecButton="true"
            {/if}
             data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
             data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedExpressCheckout action=createPayment forceSecure}"
             data-detailPage="true">
        </div>
    </div>
{/block}
