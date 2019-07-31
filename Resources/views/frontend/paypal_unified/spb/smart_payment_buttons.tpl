{block name='paypal_unified_spb_checkout_container'}
    <div class="paypal-unified-in-context--outer-button-container">
        {block name='paypal_unified_spb_checkout_container_inner'}
            <div class="paypal-unified--smart-payment-buttons"
                 data-paypalUnifiedSmartPaymentButtons="true"
                 data-clientId="{$paypalUnifiedSpbClientId}"
                 data-currency="{$paypalUnifiedSpbCurrency}"
                 data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedSmartPaymentButtons action=createPayment forceSecure}"
                 data-approvePaymentUrl="{url module=frontend controller=PaypalUnified action=return forceSecure}">
            </div>
        {/block}
    </div>
{/block}
