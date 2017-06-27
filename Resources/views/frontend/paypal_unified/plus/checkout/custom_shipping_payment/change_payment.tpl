{block name='frontend_checkout_paypal_unified_custom_payment_selection'}
    <div class="paypal--payment-selection" data-restylePaymentSelection="true">
        <div class="panel--body is--wide block-group">
            {foreach $sPayments as $payment_mean}
                {include file='frontend/paypal_unified/plus/checkout/custom_shipping_payment/custom_payment_method.tpl' payment_mean=$payment_mean}
            {/foreach}
        </div>
    </div>
{/block}
