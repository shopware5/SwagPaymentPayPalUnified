//{block name="backend/paypal_unified/model/unified_order"}
Ext.define('Shopware.apps.PaypalUnified.model.UnifiedOrder', {

    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/paypal_unified/model/unified_order/fields"}{/block}
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
     * @array
     */
    associations: [
        { type:'hasMany', model:'Shopware.apps.Base.model.Customer', name:'getCustomer', associationKey:'customer' },
        { type:'hasMany', model:'Shopware.apps.Base.model.Shop', name:'getShop', associationKey:'shop' },
        { type:'hasMany', model:'Shopware.apps.Base.model.OrderStatus', name:'getOrderStatus', associationKey:'orderStatus' },
        { type:'hasMany', model:'Shopware.apps.Order.model.Payment', name:'getPayment', associationKey:'payment' },
        { type:'hasMany', model:'Shopware.apps.Base.model.PaymentStatus', name:'getPaymentStatus', associationKey:'paymentStatus' }
    ],

    /**
     * @returns { Array }
     */
    configure: function () {
        return {
            controller: 'PaypalUnified'
        };
    }
});
//{/block}