{block name='paypal_unified_spb_checkout_container'}
    <div class="paypal-unified-in-context--outer-button-container">
        {block name='paypal_unified_spb_checkout_container_inner'}
            {if $marksOnly}
                <div class="is--hidden"
                     data-paypalUnifiedSmartPaymentButtons="true"
                     data-clientId="{$paypalUnifiedSpbClientId}"
                     data-marksOnly="true">
                </div>
            {else}
                <div class="paypal-unified--smart-payment-buttons"
                     data-paypalUnifiedSmartPaymentButtons="true"
                     data-clientId="{$paypalUnifiedSpbClientId}"
                     data-paypalIntent="{$paypalUnifiedIntent}"
                     data-currency="{$paypalUnifiedSpbCurrency}"
                     data-locale="{$paypalUnifiedButtonLocale}"
                     data-createOrderUrl="{url module=widgets controller=PaypalUnifiedV2SmartPaymentButtons action=createOrder forceSecure}"
                     data-returnUrl="{url module='frontend' controller='PaypalUnifiedV2' action='return' spbCheckout=1 forceSecure}"
                     data-paypalErrorPage="{url controller=checkout action=shippingPayment paypal_unified_error_code=2 forceSecure}">
                </div>
            {/if}
        {/block}
    </div>
{/block}
