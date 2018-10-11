{extends file='parent:frontend/checkout/shipping_payment_core.tpl'}

{*
    PayPal Plus integration

    Since the created payment changes on every payment or shipping selection change, we have to use the correct
    approval URL. Otherwise the initial URL will always be used, which is wrong
*}
{block name='frontend_checkout_shipping_payment_core_payment_fields'}
    {$smarty.block.parent}

    {block name='frontend_checkout_shipping_payment_core_buttons_top_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && $paypalUnifiedApprovalUrl}
            <div class="is--hidden paypal-unified--plus-approval-url">{$paypalUnifiedApprovalUrl}</div>
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
