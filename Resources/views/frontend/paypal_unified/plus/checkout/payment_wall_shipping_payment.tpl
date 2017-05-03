{block name='frontend_checkout_shipping_payment_paypal_unified_payment_wall_plugin'}
    {* Payment wall plugin *}
    <div class="is--hidden"
         data-paypaLPaymentWall="true"
         data-paypalLanguage="{$paypalPlusLanguageIso}"
         data-paypalApprovalUrl="{$paypalUnifiedApprovalUrl}"
         data-paypalCountryIso="{$sUserData.additional.country.countryiso}"
         data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}live{/if}">
    </div>
{/block}

{block name='frontend_checkout_shipping_payment_paypal_unified_payment_wall_shipping_payment_plugin'}
    {* Shipping payment plugin *}
    <div class="is--hidden"
         data-paypalPaymentWallShippingPayment="true"
         data-paypalPaymentId="{$paypalUnifiedPaymentId}">
    </div>
{/block}