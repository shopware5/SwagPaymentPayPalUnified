{block name='frontend_checkout_confirm_premiums_paypal_payment_wall'}
    {if $paypalUnifiedApprovalUrl}
        {block name='frontend_checkout_confirm_paypal_unified_payment_wall_plugin'}
            {* Unified payment wall plugin *}
            <div class="is--hidden"
                 data-paypalPaymentWall="true"
                 data-paypalLanguage="{$paypalUnifiedLanguageIso}"
                 data-paypalApprovalUrl="{$paypalUnifiedApprovalUrl}"
                 data-paypalCountryIso="{$sUserData.additional.country.countryiso}"
                 data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}live{/if}">
            </div>
        {/block}

        {block name='frontend_checkout_confirm_paypal_unified_payment_wall'}
            {* Placeholder for the payment wall iframe *}
            <div id="ppplus">
            </div>
        {/block}
    {/if}

    {block name='frontend_checkout_confirm_paypal_unified_payment_wall_confirm_plugin'}
        {* Unified confirm page plugin *}
        <div class="is--hidden"
             data-paypalPaymentWallConfirm="true"
             data-paypalAddressPatchUrl="{url controller=PaypalUnified action=patchAddress forceSecure}"
             data-paypalCameFromPaymentSelection="{$paypalUnifiedCameFromPaymentSelection}"
             data-paypalRemotePaymentId="{$paypalUnifiedRemotePaymentId}"
             data-paypalErrorPage="{url controller=checkout action=shippingPayment paypal_unified_error_code=2}">
        </div>
    {/block}
{/block}
