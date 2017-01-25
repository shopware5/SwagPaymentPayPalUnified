//{block name="backend/paypal_unified/model/payment_invoice"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentInvoice', {

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
        //{block name="backend/paypal_unified/model/payment_invoice/fields"}{/block}
        { name: 'total', type: 'float' },
        { name: 'currency', type: 'string' }
    ]
});
//{/block}