{extends file="parent:frontend/register/payment_fieldset.tpl"}

{block name="frontend_register_payment_fieldset_description"}
    {$smarty.block.parent}

    {if $payment_mean.name === "SwagPaymentPayPalUnified" && $paypalUnifiedUseSmartPaymentButtonMarks}
        {block name='frontend_register_payment_fieldset_paypal_unified_smart_payment_button_marks'}
            <div id="spbMarksContainer" class="payment--description">
            </div>

            {include file="frontend/paypal_unified/spb/smart_payment_buttons.tpl" marksOnly=true}
        {/block}
    {/if}
{/block}
