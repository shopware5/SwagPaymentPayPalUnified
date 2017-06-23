{extends file="parent:frontend/checkout/change_payment.tpl"}

{* PayPal Plus integration *}
{block name="frontend_index_header_javascript_jquery_lib"}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
    {$smarty.block.parent}
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_payment_fieldset_description'}
    {if $payment_mean.id == $paypalUnifiedPaymentId && $paypalUnifiedUsePlus}
        <div id="ppplus" class="method--description">
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* PayPal Plus integration *}
{block name="frontend_checkout_payment_content"}
    {if $paypalUnifiedRestylePaymentSelection}
        {include file="frontend/paypal_unified/plus/checkout/custom_shipping_payment/change_payment.tpl"}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
