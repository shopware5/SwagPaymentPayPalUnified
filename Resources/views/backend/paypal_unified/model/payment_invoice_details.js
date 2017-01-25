//{block name="backend/paypal_unified/model/payment_invoice_details"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentInvoiceDetails', {

    /**
     * Extends the standard Ext Model
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/payment_invoice_details/fields"}{/block}
        { name: 'subtotal', type: 'float' },
        { name: 'shipping', type: 'float' }
    ]
});
//{/block}