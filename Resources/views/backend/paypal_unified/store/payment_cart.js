//{block name="backend/paypal_unified/store/payment_cart"}
Ext.define('Shopware.apps.PaypalUnified.store.PaymentCart', {
    /**
     * @type { String }
     */
    extend: 'Ext.data.Store',

    /**
     * @type { String }
     */
    model: 'Shopware.apps.PaypalUnified.model.PaymentCartItem'
});
//{/block}