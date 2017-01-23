{block name="frontend_checkout_paypal_unified_paymentwall"}
    <div id="ppplus" class="method--description"
        {if $paypalConfirmPayment}
            data-paypal-unified-confirm-payment="true"
        {/if}
        {if $paypalPaymentWall}
            data-paypal-unified-payment-wall="true"
        {/if}
        data-paypal-unified-payment-id="{$paypalUnifiedPaymentId}"
        data-paypal-unified-approval-url="{$paypalUnifiedApprovalUrl}"
        data-paypal-unified-sandbox="{$paypalUnifiedModeSandbox}"
        data-paypal-unified-user-payment-id="{$sUserData.additional.payment.id}"
        data-paypal-unified-country-iso="{$sUserData.additional.country.countryiso}">
    </div>
{/block}