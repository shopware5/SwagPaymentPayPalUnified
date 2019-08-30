{extends file="parent:frontend/account/index.tpl"}

{block name="frontend_account_index_payment_method_content"}
    {if $sUserData.additional.payment.id == $paypalUnifiedPaymentId && $paypalUnifiedUseSmartPaymentButtonMarks}
        {block name='frontend_paypal_unified_account_index_overview_smart_payment_button_marks'}
            <div class="panel--body is--wide">
                <p>
                    <span id="spbMarksContainer"></span>

                    {if !$sUserData.additional.payment.esdactive && {config name="showEsd"}}
                        {s name="AccountInfoInstantDownloads"}{/s}
                    {/if}
                </p>
            </div>
            {include file="frontend/paypal_unified/spb/smart_payment_buttons.tpl" marksOnly=true}
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
