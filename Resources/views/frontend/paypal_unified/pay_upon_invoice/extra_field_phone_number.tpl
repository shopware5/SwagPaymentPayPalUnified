{if $showPayUponInvoicePhoneField}
    <div class="extra-fields--phone-number">
        <label for="puiTelephoneNumber">{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/phoneNumber"}Telephone number{/s}</label>
        <input type="tel"
               data-swagPuiTelephoneNumberField="true"
               id="puiTelephoneNumber"
               class="pui-extra-field pui--phone"
               name="puiTelephoneNumber"
               value="{$payUponInvoicePhoneFieldValue}"
               autocomplete="section-personal tel"
               required="required"
               aria-label="{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/phoneNumber"}Telephone number{/s}"
               placeholder="{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/phoneNumber"}Telephone number{/s}"/>
    </div>
{/if}
