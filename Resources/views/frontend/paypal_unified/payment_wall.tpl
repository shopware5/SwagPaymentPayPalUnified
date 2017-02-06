{block name="frontend_checkout_paypal_unified_paymentwall"}
    <div id="ppplus" class="method--description"
        {if $paypalPaymentWall}
            data-paypalUnifiedPaymentWall="true"
        {/if}
        data-paypalUnifiedPaymentId="{$paypalUnifiedPaymentId}"
        data-paypalUnifiedApprovalUrl="{$paypalUnifiedApprovalUrl}"
        data-paypalUnifiedSandbox="{$paypalUnifiedModeSandbox}"
        data-paypalUnifiedUserPaymentId="{$sUserData.additional.payment.id}"
        data-paypalUnifiedAddressPatchUrl="{url controller=PaypalUnified action=patchAddress forceSecure=true}"
        data-paypalUnifiedRemotePaymentId="{$paypalUnifiedRemotePaymentId}"
        data-paypalUnifiedCountryIso="{$sUserData.additional.country.countryiso}"
        data-paypalUnifiedErrorPage="{url controller=checkout action=shippingPayment paypal_unified_error_code=2 forceSecure=true}">
    </div>
{/block}