{extends file="parent:frontend/register/payment_fieldset.tpl"}

{block name="frontend_register_payment_fieldset_input_label"}
    {if $paypalUnifiedUseSmartPaymentButtonMarks && $payment_mean.id == $paypalUnifiedPaymentId}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_register_payment_fieldset_description"}
    {if $paypalUnifiedUseSmartPaymentButtonMarks && $payment_mean.id == $paypalUnifiedPaymentId}
        {block name='frontend_register_payment_fieldset_paypal_unified_smart_payment_button_marks'}
            <div class="payment--description">
                {include file="string:{$payment_mean.additionaldescription}"}
            </div>
            {include file="frontend/paypal_unified/spb/smart_payment_buttons.tpl" marksOnly=true}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
