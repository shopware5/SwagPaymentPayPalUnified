{block name='paypal_unified_sepa_checkout_container'}
    <div class="paypal-unified-in-context--outer-button-container right">
        {block name='paypal_unified_sepa_checkout_container_inner'}
                <div class="paypal-unified--sepa-payment-buttons"
                     data-paypalUnifiedSepa="true"
                     data-clientId="{$paypalUnifiedSpbClientId}"
                     data-currency="{$paypalUnifiedSpbCurrency}"
                     data-intent="{$paypalUnifiedSpbIntent}"
                     data-shape="{$paypalUnifiedSpbStyleShape}"
                     data-size="{$paypalUnifiedSpbStyleSize}"
                     data-locale="{$paypalUnifiedSpbButtonLocale}"
                     data-createOrderUrl="{url module=widgets controller=PaypalUnifiedV2SmartPaymentButtons action=createOrder forceSecure}"
                     data-paypalErrorPageUrl="{url controller=checkout action=shippingPayment paypal_unified_error_code=2}"
                     data-checkoutConfirmUrl="{url module=frontend controller=checkout action=confirm spbCheckout=1 sepaCheckout=1 forceSecure}">
                </div>
        {/block}
    </div>
{/block}
