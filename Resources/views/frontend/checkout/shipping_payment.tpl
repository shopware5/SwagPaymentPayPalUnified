{extends file="parent:frontend/checkout/shipping_payment.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
{/block}

{block name='frontend_checkout_payment_fieldset_description'}
    {if $paypalUnifiedApprovalUrl && $payment_mean.id == $paypalUnifiedPaymentId && $usePayPalPlus}
        {include file="frontend/paypal_unified/payment_wall.tpl"  paypalPaymentWall=true}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name='frontend_account_payment_error_messages'}
    {if $paypal_unified_error_code}
        {include file="frontend/paypal_unified/checkout/shipping_payment.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}