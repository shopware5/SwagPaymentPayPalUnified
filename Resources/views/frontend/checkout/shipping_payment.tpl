{extends file='parent:frontend/checkout/shipping_payment.tpl'}

{* PayPal Plus integration *}
{block name='frontend_index_header_javascript_jquery_lib'}
    {block name='frontend_index_header_javascript_jquery_lib_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl}
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
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl}
            {include file='frontend/paypal_unified/plus/checkout/payment_wall_shipping_payment.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}
