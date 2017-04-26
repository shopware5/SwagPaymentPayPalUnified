{extends file="parent:frontend/checkout/shipping_payment.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
{/block}

{*
    We have to overwrite the index content, since the payment selection
    itself will be reloaded dynamically. In this case we would lose our plugins.
*}
{block name="frontend_index_content"}

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
    {$smarty.block.parent}
{/block}

{block name='frontend_checkout_payment_fieldset_description'}
    {if $paypalUnifiedApprovalUrl && $payment_mean.id == $paypalUnifiedPaymentId && $usePayPalPlus}
        {block name='frontend_checkout_shipping_payment_paypal_unified_payment_wall'}
            {* This is the placeholder for the payment wall *}
            <div id="ppplus" class="method--description">
            </div>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_account_payment_error_messages'}
    {if $paypal_unified_error_code}
        {include file="frontend/paypal_unified/checkout/error_message.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}