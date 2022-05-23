{block name="paypal_unified_advanced_credit_debit_card_form_wrapper"}

    <div class="container">
        <div id="paypal-acdc-form"
             class="is--hidden"
             data-swagPayPalUnifiedAdvancedCreditDebitCard="true"
             data-clientId="{$clientId|escapeHtml}"
             data-clientToken="{$clientToken|escapeHtml}"
             data-cardHolderData="{$cardHolderData|json_encode|escapeHtml}"
             data-currency="{$paypalUnifiedCurrency}"
             data-locale="{$paypalUnifiedButtonLocale}"
             data-createOrderUrl="{url module='widgets' controller='PaypalUnifiedV2AdvancedCreditDebitCard' action='createOrder' forceSecure}"
             data-captureUrl="{url module='widgets' controller='PaypalUnifiedV2AdvancedCreditDebitCard' action='capture' forceSecure}"
             data-createOrderUrlFallback="{url module='widgets' controller='PaypalUnifiedV2SmartPaymentButtons' action='createOrder' forceSecure}"
             data-checkoutConfirmUrlFallback="{url module='frontend' controller='checkout' action='confirm' spbCheckout=1 acdcCheckout=1 forceSecure}"
             data-paypalErrorPage="{url module="frontend" controller="checkout" action="shippingPayment" paypal_unified_error_code=2 forceSecure}"
             data-placeholderCardNumber="{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/cardNumber"}Card number{/s}"
             data-placeholderExpiryDate="{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/expiryDate"}Expiry date (MM/YY){/s}"
             data-placeholderSecurityCode="{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/cvv"}Security code (CVV){/s}">
            <div class="paypal--acdc-submit-error is--hidden">
                {include file='frontend/_includes/messages.tpl' type='error' content="{s namespace='frontend/paypal_unified/checkout/confirm' name="fields/submitError"}Please check your credit card details.{/s}"}
            </div>
            <div>
                <label for="paypal-acdc-number"
                       class="is--hidden">{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/cardNumber"}Card number{/s}
                </label>
                <div id="paypal-acdc-number" class="field number-field"></div>
            </div>
            <div class="acdc-column-container container">
                <div>
                    <label for="paypal-acdc-expiration"
                           class="is--hidden">{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/expiryDate"}Expiry date (MM/YY){/s}
                    </label>
                    <div id="paypal-acdc-expiration" class="field expiration-field"></div>
                </div>
                <div>
                    <label for="paypal-acdc-cvv"
                           class="is--hidden">{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/cvv"}Security code (CVV){/s}
                    </label>
                    <div id="paypal-acdc-cvv" class="field cvv-field"></div>
                </div>
            </div>
            {if $extendedFields}
                <div>
                    <label for="card-holder-name" class="is--hidden">
                        {s namespace="frontend/paypal_unified/checkout/confirm" name="fields/cardHolderName"}Card holder name{/s}
                    </label>
                    <input type="text"
                           id="card-holder-name"
                           name="card-holder-name"
                           autocomplete="off"
                           placeholder="{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/cardHolderName"}Card holder name{/s}"
                           value="{$cardHolderData.cardHolderName|escapeHtml}"/>
                </div>
                <div>
                    <label for="card-billing-address-zip" class="is--hidden">
                        {s namespace="frontend/paypal_unified/checkout/confirm" name="fields/zipCode"}Zip / postal code{/s}
                    </label>
                    <input type="text"
                           id="card-billing-address-zip"
                           name="card-billing-address-zip"
                           autocomplete="off"
                           placeholder="{s namespace="frontend/paypal_unified/checkout/confirm" name="fields/zipCode"}Zip / postal code{/s}"
                           value="{$cardHolderData.billingAddress.postalCode|escapeHtml}"/>
                </div>
            {/if}
        </div>
    </div>
{/block}
