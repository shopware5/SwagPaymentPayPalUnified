{namespace name="frontend/paypal_unified/checkout/finish"}
{block name="frontend_checkout_finish_swag_payment_paypal_unified_head"}
    <table>
        <tr>
            {block name="frontend_checkout_finish_swag_payment_paypal_unified_head_amount"}
                <td class="unified-header--left-td"><h3>{$paypalUnifiedPaymentInstructions.amount|currency}</h3></td>
            {/block}
            {block name="frontend_checkout_finish_swag_payment_paypal_unified_head_arrow_image"}
                <td class="unified-header--center-td"><img class="" src="{link file='frontend/_public/src/img/unified-pui-arrow.png'}"></td>
            {/block}
            {block name="frontend_checkout_finish_swag_payment_paypal_unified_head_pui_image"}
                <td class="unified-header--right-td"><img class="" src="{link file='frontend/_public/src/img/sidebar-paypal-generic.png'}"/></td>
            {/block}
        </tr>
    </table>
{/block}

{block name="frontend_checkout_finish_swag_payment_paypal_unified_instruction"}
    <div class="unified--instruction">
        {s name='instructions/pleaseTransfer'}Please transfer{/s} {$paypalUnifiedPaymentInstructions.amount|currency}
        {s name='instructions/until'}until{/s} {$paypalUnifiedPaymentInstructions.dueDate|date_format: "%d.%m.%Y"}
        {s name='instructions/to'}to PayPal.{/s}
    </div>
{/block}