//{block name="backend/paypal_unified/store/unified_order"}
Ext.define('Shopware.apps.PaypalUnified.store.UnifiedOrder', {
    /**
     * extends from the standard ExtJs store class
     * @string
     */
    extend: 'Shopware.store.Listing',

    /**
     * the model which belongs to the store
     * @string
     */
    model: 'Shopware.apps.PaypalUnified.model.UnifiedOrder',

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