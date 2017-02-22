//{block name="backend/paypal_unified/model/payment_cart_item"}
Ext.define('Shopware.apps.PaypalUnified.model.PaymentCartItem', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/payment_cart_item/fields"}{/block}
        { name: 'name', type: 'string' },
        { name: 'sku', type: 'string' },
        { name: 'price', type: 'float' },
        { name: 'currency', type: 'string' },
        { name: 'quantity', type: 'int' }
    ]
});
//{/block}