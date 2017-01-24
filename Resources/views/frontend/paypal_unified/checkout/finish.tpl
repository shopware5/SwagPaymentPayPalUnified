{block name='frontend_checkout_finish_paypal_unified_instructions'}
    {if $paypalUnifiedPaymentInstructions}
        <div class="unified--panel panel has--border is--rounded">
            <div class="panel--body">
                {include file="frontend/paypal_unified/checkout/instructions/head.tpl"}
                {include file="frontend/paypal_unified/checkout/instructions/table.tpl"}
                {include file="frontend/paypal_unified/checkout/instructions/legal_message.tpl"}
            </div>
        </div>
    {/if}
{/block}