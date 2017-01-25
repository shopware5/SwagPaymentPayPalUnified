//{block name="backend/paypal_unified/model/order"}
Ext.define('Shopware.apps.PaypalUnified.model.Order', {

    /**
     * Extends the standard Ext Model
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/order/fields"}{/block}
        { name: 'number', type: 'string' },
        { name: 'invoiceAmount', type: 'float' },
        { name: 'shopId', type: 'string' },
        { name: 'customerId', type: 'string' },
        { name: 'orderTime', type: 'datetime' },
        { name: 'status', type: 'string' },
        { name: 'cleared', type: 'string' },
        { name: 'paymentId', type: 'string' },
        { name: 'currency', type: 'string' },
        { name: 'transactionId', type: 'string' },
        { name: 'temporaryId', type: 'string' }
    ],

    /**
     * @type { Array }
     */
    associations: [
        { type:'hasMany', model:'Shopware.apps.Base.model.Customer', name:'getCustomer', associationKey:'customer' },
        { type:'hasMany', model:'Shopware.apps.Base.model.Shop', name:'getShop', associationKey:'shop' },
        { type:'hasMany', model:'Shopware.apps.Base.model.OrderStatus', name:'getOrderStatus', associationKey:'orderStatus' },
        { type:'hasMany', model:'Shopware.apps.Order.model.Payment', name:'getPayment', associationKey:'payment' },
        { type:'hasMany', model:'Shopware.apps.Base.model.PaymentStatus', name:'getPaymentStatus', associationKey:'paymentStatus' }
    ]
});
//{/block}