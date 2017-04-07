// {block name="backend/paypal_unified/model/payment_customer"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentCustomer', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        // {block name="backend/paypal_unified/model/payment_customer/fields"}{/block}
        { name: 'email', type: 'string' },
        { name: 'first_name', type: 'string' },
        { name: 'last_name', type: 'string' },
        { name: 'payer_id', type: 'string' },
        { name: 'phone', type: 'string' },
        { name: 'country_code', type: 'string' }
    ]
});
// {/block}
