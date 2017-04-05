// {block name="backend/paypal_unified/model/payment"}
Ext.define('Shopware.apps.PaypalUnified.model.Payment', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        // {block name="backend/paypal_unified/model/payment/fields"}{/block}
        { name: 'intent', type: 'string' },
        { name: 'id', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'cart', type: 'string' },
        { name: 'create_time', type: 'string' },
        { name: 'update_time', type: 'string' }
    ]
});
// {/block}
