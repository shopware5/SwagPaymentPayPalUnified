{extends file='parent:frontend/checkout/confirm.tpl'}

{block name='frontend_checkout_confirm_error_messages'}
    {if $paypalUnifiedSpbCheckout}
        {include file='frontend/_includes/messages.tpl' type='success' content="{s namespace='frontend/paypal_unified/checkout/messages' name="success/spbPaymentCreated"}Your payment has been created. Please complete it, by confirming your order.{/s}"}
    {/if}

    {$smarty.block.parent}
{/block}

{* PayPal Plus integration *}
{block name='frontend_index_header_javascript_jquery_lib'}
    {block name='frontend_index_header_javascript_jquery_lib_paypal_unified_plus'}
        {if $paypalUnifiedUsePlus}
            <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{* SPB marks integration *}
{block name='frontend_checkout_confirm_left_payment_method'}
    {if $sUserData.additional.payment.id == $paypalUnifiedPaymentId && ($paypalUnifiedSpbCheckout || $paypalUnifiedUseSmartPaymentButtons)}
        <p class="payment--method-info">
            <strong class="payment--title">{s name="ConfirmInfoPaymentMethod" namespace="frontend/checkout/confirm"}{/s}</strong>
            <span id="spbMarksContainer" class="payment--method-info"></span>
        </p>

        {if !$sUserData.additional.payment.esdactive && {config name="showEsd"}}
            <p class="payment--confirm-esd">{s name="ConfirmInfoInstantDownload" namespace="frontend/checkout/confirm"}{/s}</p>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
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

{* PayPal In-Context and SPB integration *}
{block name='frontend_checkout_confirm_submit'}
    {block name='frontend_checkout_confirm_submit_paypal_unified_in_context'}
        {if !$paypalUnifiedExpressCheckout && !$paypalUnifiedUsePlus && $paypalUnifiedUseInContext && $sUserData.additional.payment.id == $paypalUnifiedPaymentId}
            {$smarty.block.parent}
            {block name='frontend_paypal_unified_in_context_confirm_submit_button'}
                {include file='frontend/paypal_unified/in_context/button.tpl'}
            {/block}
        {elseif $paypalUnifiedUseSmartPaymentButtons && !$paypalUnifiedExpressCheckout && !$paypalUnifiedUsePlus && !$paypalUnifiedUseInContext && $sUserData.additional.payment.id == $paypalUnifiedPaymentId}
            {block name='frontend_paypal_unified_confirm_smart_payment_buttons'}
                {include file="frontend/paypal_unified/spb/smart_payment_buttons.tpl"}
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
        {elseif $paypalUnifiedSpbCheckout}
            {include file='frontend/paypal_unified/spb/confirm_inputs.tpl'}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}

{*No premium items should be available*}
{block name='frontend_checkout_confirm_premiums'}
    {block name='frontend_checkout_confirm_premiums_paypal_unified_express_checkout'}
        {if $paypalUnifiedExpressCheckout || $paypalUnifiedSpbCheckout}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{*Do not allow deletion of items*}
{block name='frontend_checkout_cart_item_delete_article'}
    {block name='frontend_checkout_cart_item_delete_article_paypal_unified_express_checkout'}
        {if $paypalUnifiedExpressCheckout || $paypalUnifiedSpbCheckout}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}

{* Disable item quantity selection *}
{block name='frontend_checkout_cart_item_quantity_selection'}
    {block name='frontend_checkout_cart_item_quantity_selection_paypal_unified_express_checkout'}
        {if $paypalUnifiedExpressCheckout || $paypalUnifiedSpbCheckout}
            {include file='frontend/paypal_unified/express_checkout/confirm/quantity_selection.tpl'}
        {else}
            {$smarty.block.parent}
        {/if}
    {/block}
{/block}
