{block name='frontend_checkout_confirm_premiums_paypal_unified_pay_upon_invoice_wrapper'}
    <div class="pay-upon-invoice--extra-fields">
        {block name='paypal_unified_pay_upon_invoice_confirm_birthday'}
            {include file='frontend/paypal_unified/pay_upon_invoice/extra_field_birthday.tpl'}
        {/block}

        {block name='paypal_unified_pay_upon_invoice_confirm_phone_number'}
            {include file='frontend/paypal_unified/pay_upon_invoice/extra_field_phone_number.tpl'}
        {/block}
    </div>
{/block}
