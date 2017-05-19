{extends file="parent:frontend/checkout/shipping_payment.tpl"}

{* PayPal Plus integration *}
{block name="frontend_index_header_javascript_jquery_lib"}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
    {$smarty.block.parent}
{/block}

{*
    PayPal Plus integration

    We have to overwrite the index content, since the payment selection
    itself will be reloaded dynamically. In this case we would lose our plugins.
*}
{block name="frontend_index_content"}
    {include file="frontend/paypal_unified/plus/checkout/payment_wall_shipping_payment.tpl"}

    {$smarty.block.parent}
{/block}

{* PayPal Plus integration *}
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

{* All integrations *}
{block name='frontend_account_payment_error_messages'}
    {if $paypalUnifiedErrorCode}
        {include file="frontend/paypal_unified/checkout/error_message.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}
