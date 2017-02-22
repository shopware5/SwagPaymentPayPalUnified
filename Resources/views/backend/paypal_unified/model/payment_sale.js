//{block name="backend/paypal_unified/model/payment_sale"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentSale', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/payment_sale/fields"}{/block}
        { name: 'id', type: 'string' },
        { name: 'amount', type: 'float' },
        { name: 'create_time', type: 'string' },
        { name: 'update_time', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'currency', type: 'string' },
        { name: 'type', type: 'string' }
    ]
});
//{/block}