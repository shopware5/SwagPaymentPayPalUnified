//{block name="backend/paypal_unified/model/capture"}
Ext.define('Shopware.apps.PaypalUnified.model.Capture', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/capture/fields"}{/block}
        { name: 'id', type: 'string'},
        { name: 'create_time', type: 'string' },
        { name: 'update_time', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'payment_mode', type: 'string' }
    ]
});
//{/block}