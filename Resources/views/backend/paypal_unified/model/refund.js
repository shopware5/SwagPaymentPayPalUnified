//{block name="backend/paypal_unified/model/refund"}
Ext.define('Shopware.apps.PaypalUnified.model.Refund', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/refund/fields"}{/block}
        { name: 'id', type: 'string' },
        { name: 'create_time', type: 'string' },
        { name: 'update_time', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'payment_mode', type: 'string' },
        { name: 'invoice_number', type: 'string' }
    ]
});
//{/block}