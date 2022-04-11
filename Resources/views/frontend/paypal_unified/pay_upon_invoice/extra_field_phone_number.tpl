{if $showPayUponInvoicePhoneField}
    <div class="extra-fields--phone-number">
        <input type="tel"
               id="puiTelephoneNumber"
               class="pui-extra-field pui--phone"
               name="puiTelephoneNumber"
               autocomplete="off"
               required="required"
               aria-label="{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/phoneNumber"}Telephone number{/s}"
               placeholder="{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/phoneNumber"}Telephone number{/s}"/>
    </div>
{/if}
