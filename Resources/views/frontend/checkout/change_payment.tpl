{extends file='parent:frontend/checkout/change_payment.tpl'}

{* PayPal Plus integration *}
{block name='frontend_index_header_javascript_jquery_lib'}
    {block name='frontend_index_header_javascript_jquery_lib_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl}
            <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_payment_fieldset_description'}
    {block name='frontend_checkout_payment_fieldset_description_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl && $payment_mean.id == $paypalUnifiedPaymentId}
            <div id="ppplus" class="method--description">
            </div>
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
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
