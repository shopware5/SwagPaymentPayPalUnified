//{block name="backend/paypal_unified/model/payment_customer_shipping"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentCustomerShipping', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/payment_customer_shipping/fields"}{/block}
        { name: 'recipient_name', type: 'string' },
        { name: 'line1', type: 'string' },
        { name: 'city', type: 'string' },
        { name: 'state', type: 'string' },
        { name: 'postal_code', type: 'string' },
        { name: 'country_code', type: 'string' }
    ]
});
//{/block}