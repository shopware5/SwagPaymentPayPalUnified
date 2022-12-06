{block name='paypal_unified_sepa_checkout_container'}
    <div class="paypal-unified-sepa--outer-button-container">
        {block name='paypal_unified_sepa_checkout_container_inner'}
                <div class="paypal-unified--sepa-payment-buttons right"
                     data-paypalUnifiedSepa="true"
                     data-clientId="{$paypalUnifiedSpbClientId}"
                     data-currency="{$paypalUnifiedSpbCurrency}"
                     data-intent="{$paypalUnifiedSpbIntent}"
                     data-shape="{$paypalUnifiedSpbStyleShape}"
                     data-size="{$paypalUnifiedSpbStyleSize}"
                     data-locale="{$paypalUnifiedSpbButtonLocale}"
                     data-createOrderUrl="{url module=widgets controller=PaypalUnifiedV2SmartPaymentButtons action=createOrder forceSecure}"
                     data-paypalErrorPage="{url controller=checkout action=shippingPayment paypal_unified_error_code=2}"
                     data-returnUrl="{url module='frontend' controller='PaypalUnifiedV2' action='return' spbCheckout=1 sepaCheckout=1 forceSecure}">
                </div>
        {/block}
    </div>
{/block}
