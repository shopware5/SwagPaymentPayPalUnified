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

{block name="frontend_checkout_confirm_submit"}
    {if $sPayment.name === 'SwagPaymentPayPalUnifiedInstallments' && !$paypalSelectedInstallment}
        {block name="frontend_paypal_unified_installments_cart_submit_button"}
            <button type="submit" class="btn is--primary is--large right is--icon-right" form="confirm--form" data-preloader-button="true">
                {s namespace="frontend/paypal_unified/checkout/confirm" name="buyButton/confirmText"}{/s}<i class="icon--arrow-right"></i>
            </button>
        {/block}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_checkout_cart_footer_field_labels_sum"}
    {block name="frontend_paypal_unified_installments_cart_price_overview_shipping"}
        <li class="list--entry block-group entry--shipping">

            {block name='frontend_paypal_unified_installments_cart_price_overview_shipping_label'}
                <div class="entry--label block">
                    {s namespace="frontend/checkout/cart_footer" name="CartFooterLabelShipping"}{/s}
                </div>
            {/block}

            {block name='frontend_paypal_unified_installments_cart_price_overview_shipping_value'}
                <div class="entry--value block">
                    {$sShippingcosts|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                </div>
            {/block}
        </li>
    {/block}

    {block name="frontend_paypal_unified_installments_cart_price_overview_sum"}
        <li class="list--entry block-group entry--sum">

            {block name='frontend_paypal_unified_installments_cart_price_overview_sum_label'}
                <div class="entry--label block entry--total">
                    {s namespace="frontend/checkout/cart_footer" name="CartFooterLabelSum"}{/s}
                </div>
            {/block}

            {block name='frontend_paypal_unified_installments_cart_price_overview_value'}
                <div class="entry--value block entry--total">
                    {$sBasket.Amount|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                </div>
            {/block}
        </li>
    {/block}
{/block}

{block name="frontend_checkout_cart_footer_field_labels_taxes"}
    {$smarty.block.parent}
    {if $paypalInstallmentsMode === 'selected'}
        {block name="frontend_paypal_unified_installments_cart_price_overview_financing_value"}
            <li class="list--entry block-group entry--installments-rate">
                {block name="frontend_paypal_unified_installments_cart_price_financing_value_label"}
                    <div class="entry--label block entry--total">
                        {s namespace='frontend/paypal_unified/checkout/confirm' name="FinancingValueLabel"}{/s}
                    </div>
                {/block}

                {block name="frontend_paypal_unified_installments_cart_price_financing_value_value"}
                    <div class="entry--value block entry--total">
                        &lt;INSERT RATE HERE&gt;
                    </div>
                {/block}
            </li>
        {/block}

        {block name="frontend_paypal_unified_installments_cart_price_total_value"}
            <li class="list--entry block-group entry--installments-sum">
                {block name="frontend_paypal_unified_installments_cart_price_total_value_label"}
                    <div class="entry--label block entry--total">
                        {s namespace='frontend/paypal_unified/checkout/confirm' name="FinancingValueTotalLabel"}{/s}
                    </div>
                {/block}

                {block name="frontend_paypal_unified_installments_cart_price_total_value_value"}
                    <div class="entry--value block entry--total">
                        &lt;INSERT RATE HERE&gt;
                    </div>
                {/block}
            </li>
        {/block}
    {/if}
{/block}

{block name="frontend_checkout_confirm_tos_panel"}
    {block name="frontend_paypal_unified_installments_tos"}
        {include file="frontend/paypal_unified/installments/confirm_financing_header.tpl"}
    {/block}
{/block}

{block name="frontend_checkout_cart_footer_field_labels_shipping"}{/block}

{block name="frontend_checkout_cart_footer_field_labels_total"}{/block}

{block name="frontend_checkout_cart_footer_field_labels_totalnet"}{/block}

{block name="frontend_checkout_confirm_information_addresses_billing_panel_actions"}{/block}

{block name="frontend_checkout_confirm_information_addresses_shipping_panel_actions"}{/block}

{block name="frontend_checkout_confirm_left_payment_method_actions"}{/block}