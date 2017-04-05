// {block name="backend/paypal_unified/model/payment_amount_details"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentAmountDetails', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        // {block name="backend/paypal_unified/model/payment_amount_details/fields"}{/block}
        { name: 'subtotal', type: 'float' },
        { name: 'shipping', type: 'float' }
    ]
});
// {/block}
