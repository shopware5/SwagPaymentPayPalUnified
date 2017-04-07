// {block name="backend/paypal_unified/model/sale"}
Ext.define('Shopware.apps.PaypalUnified.model.Sale', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        // {block name="backend/paypal_unified/model/sale/fields"}{/block}
        { name: 'id', type: 'string' },
        { name: 'create_time', type: 'string' },
        { name: 'update_time', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'payment_mode', type: 'string' }
    ]
});
// {/block}
