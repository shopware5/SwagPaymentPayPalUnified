{extends file="parent:frontend/checkout/confirm.tpl"}

{* Hide invalid cart labels *}
{block name="frontend_checkout_cart_footer_field_labels_sum"}{/block}
{block name="frontend_checkout_cart_footer_field_labels_totalnet"}{/block}

{*Address and payment method can not be changed*}
{block name="frontend_checkout_confirm_information_addresses_billing_panel_actions"}{/block}
{block name="frontend_checkout_confirm_information_addresses_shipping_panel_actions"}{/block}
{block name='frontend_checkout_confirm_information_addresses_equal_panel_actions'}{/block}
{block name="frontend_checkout_confirm_information_addresses_equal_panel_shipping_select_address"}{/block}
{block name="frontend_checkout_confirm_left_payment_method_actions"}{/block}

{*No premium items should be available*}
{block name='frontend_checkout_confirm_premiums'}{/block}

{*Do not request any financing options*}
{block name='frontend_detail_buy_paypal_unified_installments_ajax'}{/block}

{*Do not allow deletion of items*}
{block name='frontend_checkout_cart_item_delete_article'}{/block}

{block name="frontend_checkout_confirm_tos_panel"}
    {block name="frontend_paypal_unified_installments_tos"}
        {include file="frontend/paypal_unified/installments/return/confirm_header.tpl"}
    {/block}
{/block}

{* Disable item quantity selection *}
{block name='frontend_checkout_cart_item_quantity_selection'}
    {if !$sBasketItem.additional_details.laststock || ($sBasketItem.additional_details.laststock && $sBasketItem.additional_details.instock > 0)}
        <select name="sQuantity" disabled="disabled">
            <option value="{$sBasketItem.quantity}" selected="selected">
                {$sBasketItem.quantity}
            </option>
        </select>
    {else}
        {s name="CartColumnQuantityEmpty" namespace="frontend/checkout/cart_item"}{/s}
    {/if}
{/block}

{* Reword "total sum" label to "sum" *}
{block name='frontend_checkout_cart_footer_field_labels_total_label'}
    <div class="entry--label block">
        {s name="Sum" namespace="frontend/paypal_unified/installments/return/confirm"}Sum{/s}
    </div>
{/block}

{* Display financing information *}
{block name="frontend_checkout_cart_footer_field_labels_taxes"}
    {block name="frontend_paypal_unified_installments_cart_price_overview_financing_value"}
        <li class="list--entry block-group entry--installments-rate">
            {block name="frontend_paypal_unified_installments_cart_price_financing_value_label"}
                <div class="entry--label block entry--total">
                    {s namespace='frontend/paypal_unified/installments/return/confirm' name="FinancingValueLabel"}{/s}
                </div>
            {/block}

            {block name="frontend_paypal_unified_installments_cart_price_financing_value_value"}
                <div class="entry--value block entry--total">
                    {$paypalInstallmentsCredit.total_interest.value|currency}
                </div>
            {/block}
        </li>
    {/block}

    {block name="frontend_paypal_unified_installments_cart_price_total_value"}
        <li class="list--entry block-group entry--installments-sum">
            {block name="frontend_paypal_unified_installments_cart_price_total_value_label"}
                <div class="entry--label block entry--total">
                    {s namespace='frontend/paypal_unified/installments/return/confirm' name="FinancingValueTotalLabel"}{/s}
                </div>
            {/block}

            {block name="frontend_paypal_unified_installments_cart_price_total_value_value"}
                <div class="entry--value block entry--total">
                    {$paypalInstallmentsCredit.total_cost.value|currency}
                </div>
            {/block}
        </li>
    {/block}
{/block}

{* Reset the submit button text  *}
{block name="frontend_checkout_confirm_submit"}
    <a class="button btn is--primary is--large right is--icon-right" href="{url controller=PaypalUnified action=return paymentId=$paypalInstallmentsPaymentId PayerID=$paypalInstallmentsPayerId basketId=$paypalInstallmentsBasketId forceSecure}">
        {s name='ConfirmActionSubmit' namespace="frontend/checkout/confirm"}{/s}<i class="icon--arrow-right"></i>
    </a>
{/block}

{* The Plus tax label should now be called incl. tax *}
{block name='frontend_checkout_cart_footer_field_labels_taxes_label'}
    <div class="entry--label block">
        {s name="CartFooterTotalTax" namespace="frontend/paypal_unified/installments/return/confirm"}{/s}
    </div>
{/block}
