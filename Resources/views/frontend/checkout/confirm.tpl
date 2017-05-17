{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_confirm_premiums'}
    {if $usePayPalPlus && $sUserData.additional.payment.id == $paypalUnifiedPaymentId }
        {include file="frontend/paypal_unified/plus/checkout/payment_wall_premiums.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}

{* PayPal Installments integration *}
{block name='frontend_checkout_confirm_confirm_table_actions'}
    {if $paypalInstallmentsMode === 'cheapest' || $paypalInstallmentsRequestCompleteList}
        {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
    {/if}

    {if $paypalInstallmentsMode === 'simple' && !$paypalInstallmentsRequestCompleteList}
        {include file="frontend/paypal_unified/installments/upstream_presentment/cart/simple.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}

{* PayPal Installments integration *}
{block name="frontend_checkout_confirm_submit"}
    {if $paypalInstallmentsRequestCompleteList}
        {block name="frontend_paypal_unified_installments_cart_submit_button"}
            <button type="submit" class="btn is--primary is--large right is--icon-right" form="confirm--form" data-preloader-button="true">
                {s namespace="frontend/paypal_unified/checkout/confirm" name="installments/confirmButtonText"}Apply for credit{/s}<i class="icon--arrow-right"></i>
            </button>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}