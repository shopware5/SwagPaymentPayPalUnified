{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
{/block}

{block name='frontend_checkout_confirm_premiums'}
    {if $usePayPalPlus && $sUserData.additional.payment.id == $paypalUnifiedPaymentId }

        {block name='frontend_checkout_confirm_paypal_unified_payment_wall_plugin'}
            {* Unified payment wall plugin *}
            <div class="is--hidden"
                 data-paypalPaymentWall="true"
                 data-paypalLanguage="{$paypalPlusLanguageIso}"
                 data-paypalApprovalUrl="{$paypalUnifiedApprovalUrl}"
                 data-paypalCountryIso="{$sUserData.additional.country.countryiso}"
                 data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}live{/if}">
            </div>
        {/block}

        {block name='frontend_checkout_confirm_paypal_unified_payment_wall_confirm_plugin'}
            {* Unified confirm page plugin *}
            <div class="is--hidden"
                 data-paypalPaymentWallConfirm="true"
                 data-paypalAddressPatchUrl="{url controller=PaypalUnified action=patchAddress forceSecure=true}"
                 data-paypalCameFromPaymentSelection="{$cameFromPaymentSelection}"
                 data-paypalRemotePaymentId="{$paypalUnifiedRemotePaymentId}"
                 data-paypalErrorPage="{url controller=checkout action=shippingPayment paypal_unified_error_code=2}">
            </div>
        {/block}

        {block name='frontend_checkout_confirm_paypal_unified_payment_wall'}
            {* Placeholder for the payment wall iframe *}
            <div id="ppplus">
            </div>
        {/block}

    {/if}

    {$smarty.block.parent}
{/block}
