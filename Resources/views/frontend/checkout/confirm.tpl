{extends file='parent:frontend/checkout/confirm.tpl'}

{* PayPal Plus integration *}
{block name='frontend_index_header_javascript_jquery_lib'}
    {block name='frontend_index_header_javascript_jquery_lib_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus}
            <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_confirm_premiums'}
    {block name='frontend_checkout_confirm_premiums_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus && !$paypalUnifiedExpressCheckout && $sUserData.additional.payment.id == $paypalUnifiedPaymentId}
            {include file='frontend/paypal_unified/plus/checkout/payment_wall_premiums.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Installments integration *}
{block name='frontend_checkout_confirm_confirm_table_actions'}
    {block name='frontend_checkout_confirm_confirm_table_actions_paypal_unified_installments'}
        {if $paypalInstallmentsMode === 'cheapest' || $paypalInstallmentsRequestCompleteList}
            {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
        {/if}

        {if $paypalInstallmentsMode === 'simple' && !$paypalInstallmentsRequestCompleteList}
            {include file='frontend/paypal_unified/installments/upstream_presentment/cart/simple.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* PayPal Installments and In-Context integration *}
{block name='frontend_checkout_confirm_submit'}
    {block name='frontend_checkout_confirm_submit_paypal_unified_installments_and_in_context'}
        {if $paypalInstallmentsRequestCompleteList}
            {block name='frontend_paypal_unified_installments_confirm_submit_button'}
                <button type="submit" class="btn is--primary is--large right is--icon-right" form="confirm--form" data-preloader-button="true">
                    {s namespace='frontend/paypal_unified/checkout/confirm' name='installments/confirmButtonText'}Apply for credit{/s}<i class="icon--arrow-right"></i>
                </button>
            {/block}
        {elseif !$paypalUnifiedExpressCheckout && !$paypalUnifiedUsePlus && $paypalUnifiedUseInContext && $sUserData.additional.payment.id == $paypalUnifiedPaymentId}
            {$smarty.block.parent}
            {block name='frontend_paypal_unified_in_context_confirm_submit_button'}
                {include file='frontend/paypal_unified/in_context/button.tpl'}
            {/block}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* PayPal Express Checkout integration *}
{* add needed values to request *}
{block name='frontend_checkout_confirm_tos_panel'}
    {block name='frontend_checkout_confirm_tos_panel_paypal_unified_express_checkout'}
        {if $paypalUnifiedExpressCheckout}
            {include file='frontend/paypal_unified/express_checkout/confirm_inputs.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{*No premium items should be available*}
{block name='frontend_checkout_confirm_premiums'}
    {block name='frontend_checkout_confirm_premiums_paypal_unified_express_checkout'}
        {if $paypalUnifiedExpressCheckout}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{*Do not allow deletion of items*}
{block name='frontend_checkout_cart_item_delete_article'}
    {block name='frontend_checkout_cart_item_delete_article_paypal_unified_express_checkout'}
        {if !$paypalUnifiedExpressCheckout}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* Disable item quantity selection *}
{block name='frontend_checkout_cart_item_quantity_selection'}
    {block name='frontend_checkout_cart_item_quantity_selection_paypal_unified_express_checkout'}
        {if $paypalUnifiedExpressCheckout}
            {include file='frontend/paypal_unified/express_checkout/confirm/quantity_selection.tpl'}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
