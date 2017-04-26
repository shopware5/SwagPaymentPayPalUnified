{extends file="parent:frontend/checkout/change_payment.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
{/block}

{block name='frontend_checkout_payment_fieldset_description'}
    {if $payment_mean.id == $paypalUnifiedPaymentId && $usePayPalPlus}
        <div id="ppplus" class="method--description">
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_payment_content"}
    {if $restylePaymentSelection}
        <div class="paypal--payment-selection" data-restylePaymentSelection="true">
            <div class="panel--body is--wide block-group">
                {foreach $sPayments as $payment_mean}
                    {include file="frontend/paypal_unified/checkout/payment_method.tpl" payment_mean=$payment_mean}
                {/foreach}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}