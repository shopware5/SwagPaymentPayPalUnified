{block name='paypal_unified_spb_checkout_container'}
    <div class="paypal-unified-in-context--outer-button-container">
        {block name='paypal_unified_spb_checkout_container_inner'}
            {if $marksOnly}
                <div class="paypal-unified--smart-payment-buttons"
                     data-paypalUnifiedSmartPaymentButtons="true"
                     data-clientId="{$paypalUnifiedSpbClientId}"
                     data-marksOnly="true">
                </div>
            {else}
                <div class="paypal-unified--smart-payment-buttons"
                     data-paypalUnifiedSmartPaymentButtons="true"
                     data-clientId="{$paypalUnifiedSpbClientId}"
                     data-currency="{$paypalUnifiedSpbCurrency}"
                     data-createPaymentUrl="{url module=widgets controller=PaypalUnifiedSmartPaymentButtons action=createPayment forceSecure}"
                     data-checkoutConfirmUrl="{url module=frontend controller=checkout action=confirm spbCheckout=1 forceSecure}">
                </div>
            {/if}
        {/block}
    </div>
{/block}
