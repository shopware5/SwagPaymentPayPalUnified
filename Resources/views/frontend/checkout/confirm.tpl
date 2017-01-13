{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js" async></script>
{/block}

{block name='frontend_checkout_confirm_premiums'}
    {if $usePayPalPlus && !$cameFromPaymentSelection && $payment_mean.id == $paypalUnifiedPaymentId && $paypalUnifiedApprovalUrl}
        {include file="frontend/paypal_unified/payment_wall.tpl" paypalPaymentWall=true}
    {/if}
    {$smarty.block.parent}
{/block}
