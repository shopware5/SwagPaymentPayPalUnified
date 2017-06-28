{block name='frontend_checkout_confirm_paypal_unified_in_context_button'}
    <div class="paypal-unified-in-context--outer-button-container">
        {block name='frontend_checkout_confirm_paypal_unified_in_context_button_inner'}
            <div class="paypal-unified-in-context--button-container right"
                 data-paypalUnifiedNormalCheckoutButtonInContext="true"
                 data-paypalMode="{if $paypalUnifiedModeSandbox}sandbox{else}production{/if}"
                 data-label="pay"
                {block name='frontend_checkout_confirm_paypal_unified_in_context_button_data'}{/block}>
            </div>
        {/block}
    </div>
{/block}
