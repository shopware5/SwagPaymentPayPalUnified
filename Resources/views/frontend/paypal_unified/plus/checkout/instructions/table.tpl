{namespace name='frontend/paypal_unified/checkout/finish'}
{block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions'}
    <div class="unified-instructions--container">
        <table class="unified-instructions--table">
            <tbody>
            {block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions_bank'}
                <tr>
                    <td>{s name='instructions/table/bank'}Bank:{/s}</td>
                    <td class="bolder">{$paypalUnifiedPaymentInstructions.bankName}</td>
                </tr>
            {/block}
            {block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions_bic'}
                <tr>
                    <td>{s name='instructions/table/bic'}BIC:{/s}</td>
                    <td class="bolder">{$paypalUnifiedPaymentInstructions.bic}</td>
                </tr>
            {/block}
            {block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions_iban'}
                <tr>
                    <td>{s name='instructions/table/iban'}IBAN:{/s}</td>
                    <td class="bolder">{$paypalUnifiedPaymentInstructions.iban}</td>
                </tr>
            {/block}
            {block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions_holder'}
                <tr>
                    <td>{s name='instructions/table/holder'}Account holder:{/s}</td>
                    <td class="bolder">{$paypalUnifiedPaymentInstructions.accountHolder}</td>
                </tr>
            {/block}
            {block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions_amount'}
                <tr>
                    <td>{s name='instructions/table/amount'}Amount:{/s}</td>
                    <td class="bolder">{$paypalUnifiedPaymentInstructions.amount|currency}</td>
                </tr>
            {/block}
            {block name='frontend_checkout_finish_swag_payment_paypal_unified_instructions_reference'}
                <tr>
                    <td>{s name='instructions/table/reference'}Reference:{/s}</td>
                    <td class="bolder">{$paypalUnifiedPaymentInstructions.reference}</td>
                </tr>
            {/block}
            </tbody>
        </table>
    </div>
{/block}
