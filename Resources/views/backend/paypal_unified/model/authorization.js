// {block name="backend/paypal_unified/model/authorization"}
Ext.define('Shopware.apps.PaypalUnified.model.Authorization', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        // {block name="backend/paypal_unified/model/capture/fields"}{/block}
        { name: 'id', type: 'string' },
        { name: 'create_time', type: 'string' },
        { name: 'update_time', type: 'string' },
        { name: 'valid_until', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'payment_mode', type: 'string' }
    ]
});
// {/block}
