{block name='frontend_checkout_confirm_paypal_unified_in_context_button'}
    <div class="paypal-unified-in-context--outer-button-container">
        {block name='frontend_checkout_confirm_paypal_unified_in_context_button_inner'}
            <div class="paypal-unified-in-context--button-container right"
                 data-paypalUnifiedNormalCheckoutButtonInContext="true"
                 data-color="{$paypalUnifiedButtonStyleColor}"
                 data-shape="{$paypalUnifiedButtonStyleShape}"
                 data-size="{$paypalUnifiedButtonStyleSize}"
                 data-locale="{$paypalUnifiedButtonLocale}"
                 data-paypalErrorPage="{url controller='checkout' action='shippingPayment' paypal_unified_error_code=2 forceSecure}"
                 data-clientId="{$paypalUnifiedClientId}"
                 data-currency="{$paypalUnifiedCurrency}"
                 data-createOrderUrl="{url controller='PaypalUnifiedV2' action='index' forceSecure}"
                 data-returnUrl="{url module='frontend' controller='PaypalUnifiedV2' action='return' inContextCheckout=1 forceSecure}"
                 data-paypalIntent="{$paypalUnifiedIntent}"
                {block name='frontend_checkout_confirm_paypal_unified_in_context_button_data'}{/block}>
            </div>
        {/block}
    </div>
{/block}
