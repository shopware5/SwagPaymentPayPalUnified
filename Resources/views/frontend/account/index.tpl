{extends file="parent:frontend/account/index.tpl"}

{block name="frontend_account_index_payment_method_content"}
    {$smarty.block.parent}

    {if $sUserData.additional.payment.name === "SwagPaymentPayPalUnified" && $paypalUnifiedUseSmartPaymentButtonMarks}
        {block name='frontend_paypal_unified_account_index_overview_smart_payment_button_marks'}
            <div class="paypal-unified--account-overview--smart-payment-button-marks" id="spbMarksContainer" >
            </div>

            {include file="frontend/paypal_unified/spb/smart_payment_buttons.tpl" marksOnly=true}
        {/block}
    {/if}
{/block}
