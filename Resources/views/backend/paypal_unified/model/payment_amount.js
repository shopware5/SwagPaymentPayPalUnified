//{block name="backend/paypal_unified/model/payment_amount"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentAmount', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/payment_amount/fields"}{/block}
        { name: 'total', type: 'float' },
        { name: 'currency', type: 'string' }
    ]
});
//{/block}