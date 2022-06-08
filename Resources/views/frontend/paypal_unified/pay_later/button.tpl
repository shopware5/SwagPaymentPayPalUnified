{block name='frontend_checkout_confirm_paypal_unified_pay_later_button'}
    <div class="paypal-unified-pay-later--outer-button-container">
        {block name='frontend_checkout_confirm_paypal_unified_pay_later_button_inner'}
            <div class="paypal-unified-pay-later--button-container right"
                 data-paypalUnifiedPayLater="true"
                 data-color="{$paypalUnifiedPayLaterButtonStyleColor}"
                 data-shape="{$paypalUnifiedPayLaterStyleShape}"
                 data-size="{$paypalUnifiedPayLaterStyleSize}"
                 data-locale="{$paypalUnifiedPayLaterButtonLocale}"
                 data-clientId="{$paypalUnifiedPayLaterClientId}"
                 data-currency="{$paypalUnifiedPayLaterCurrency}"
                 data-paypalIntent="{$paypalUnifiedPayLaterIntent}"
                 data-paypalErrorPage="{url controller='checkout' action='shippingPayment' paypal_unified_error_code=2 forceSecure}"
                 data-createOrderUrl="{url controller='PaypalUnifiedV2' action='index' paypalUnifiedPayLater=1 forceSecure}"
                 data-confirmUrl="{url module='frontend' controller='checkout' action='confirm' paypalUnifiedPayLater=1 forceSecure}"
                    {block name='frontend_checkout_confirm_paypal_unified_pay_later_button_data'}{/block}>
            </div>
        {/block}
    </div>
{/block}
