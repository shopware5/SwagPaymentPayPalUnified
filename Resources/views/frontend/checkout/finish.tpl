{extends file='parent:frontend/checkout/finish.tpl'}

{block name='frontend_checkout_finish_teaser'}
    {block name='frontend_checkout_finish_teaser_error_messages_paypal_unified_errors'}
        {if $paypalUnifiedErrorCode}
            {include file='frontend/paypal_unified/checkout/error_message.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}

    {* PayPal Plus integration *}
    {block name='frontend_checkout_finish_teaser_paypal_unified_plus'}
        {if $paypalUnifiedPaymentInstructions}
            {include file='frontend/paypal_unified/plus/checkout/payment_instructions.tpl'}
        {/if}

        {if $pollingError}
            <div class="unified--panel panel has--border is--rounded">
                <h2 class="panel--title teaser--title is--align-center">
                    {s name='payPalInvoice/aErrorOccurred'}An error occurred while creating the payment. Please contact the shop owner.{/s}
                </h2>
            </div>
        {/if}
    {/block}

    {* PayPal Pay Upon Invoice *}
    {block name='frontend_checkout_finish_teaser_paypal_unified_plus'}
        {if $isPui && !$paypalUnifiedPaymentInstructions}
            <div data-swagPayPalUnifiedPolling="true"
                 data-pollingUrl="{$puiPollingUrl}"
                 data-successUrl="{$puiSuccessUrl}"
                 data-errorUrl="{$puiErrorUrl}"
            >
                <div class="unified--panel panel has--border is--rounded">
                    <h2 class="panel--title teaser--title is--align-center">
                        {s name='payPalInvoice/paymentBeingProcessed'}The payment is being processed{/s}
                    </h2>
                    <div class="panel--body is--wide is--align-center">
                        <p>
                            {s name='payPalInvoice/paymentBeingProcessedSubtitle'}Processing may take up to several minutes. Please do not close this window, you will be redirected.{/s}
                        </p>
                        <i class="finish--loading-indicator"></i>
                    </div>
                </div>
            </div>
        {/if}
    {/block}
{/block}
