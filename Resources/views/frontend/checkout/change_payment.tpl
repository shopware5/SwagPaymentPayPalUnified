{extends file='parent:frontend/checkout/change_payment.tpl'}

{* Smart Payment Buttons integration *}
{block name='frontend_checkout_payment_fieldset_input_label'}
    {if $paypalUnifiedUseSmartPaymentButtons && $payment_mean.id == $paypalUnifiedPaymentId}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* PayPal Plus and Smart Payment Buttons integration *}
{block name='frontend_checkout_payment_fieldset_description'}
    {block name='frontend_checkout_payment_fieldset_description_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl && $payment_mean.id == $paypalUnifiedPaymentId}
            <div id="ppplus" class="method--description">
            </div>
        {elseif $paypalUnifiedUseSmartPaymentButtons && $payment_mean.id == $paypalUnifiedPaymentId}
            {$smarty.block.parent}

            {block name='frontend_paypal_unified_confirm_smart_payment_buttons_marks'}
                {include file="frontend/paypal_unified/spb/smart_payment_buttons.tpl" marksOnly=true}
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_payment_content'}
    {block name='frontend_checkout_payment_content_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl && $paypalUnifiedRestylePaymentSelection}
            {include file='frontend/paypal_unified/plus/checkout/custom_shipping_payment/change_payment.tpl'}
        {elseif $paypalUnifiedUseSepa}
            {$smarty.block.parent}
            <div data-swagPayPalUnifiedSepaEligibility="true"
                 data-clientId="{$paypalUnifiedSpbClientId}"
                 data-intent="{$paypalUnifiedSpbIntent}"
                 data-locale="{$paypalUnifiedSpbButtonLocale}"
                 data-currency="{$paypalUnifiedSpbCurrency}"
                 data-sepaPaymentMethodId="{$paypalUnifiedSepaPaymentId}">
            </div>
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
