//{block name="backend/paypal_unified/store/order"}
Ext.define('Shopware.apps.PaypalUnified.store.Order', {
    /**
     * extends from the standard ExtJs store class
     * @type { String }
     */
    extend: 'Shopware.store.Listing',

    /**
     * the model which belongs to the store
     * @type { String }
     */
    model: 'Shopware.apps.PaypalUnified.model.ShopwareOrder',

    /**
     * @return { Object }
     */
    configure: function () {
        return {
            controller: 'PaypalUnified'
        };
    }
});
//{/block}