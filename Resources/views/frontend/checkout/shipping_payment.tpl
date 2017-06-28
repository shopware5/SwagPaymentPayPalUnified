{extends file='parent:frontend/checkout/shipping_payment.tpl'}

{* PayPal Plus integration *}
{block name='frontend_index_header_javascript_jquery_lib'}
    {block name='frontend_index_header_javascript_jquery_lib_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus}
            <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{*
    PayPal Plus integration

    We have to overwrite the index content, since the payment selection
    itself will be reloaded dynamically. In this case we would lose our plugins.
*}
{block name='frontend_index_content'}
    {block name='frontend_index_content_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus}
            {include file='frontend/paypal_unified/plus/checkout/payment_wall_shipping_payment.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_payment_fieldset_description'}
    {block name='frontend_checkout_payment_fieldset_description_paypal_unified_plus'}
        {if $paypalUnifiedApprovalUrl && $payment_mean.id == $paypalUnifiedPaymentId && $paypalUnifiedUsePlus}
            {block name='frontend_checkout_shipping_payment_paypal_unified_payment_wall'}
                {* This is the placeholder for the payment wall *}
                <div id="ppplus" class="method--description">
                </div>
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* All integrations *}
{block name='frontend_account_payment_error_messages'}
    {block name='frontend_account_payment_error_messages_paypal_unified_errors'}
        {if $paypalUnifiedErrorCode}
            {include file='frontend/paypal_unified/checkout/error_message.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}
