// {namespace name="backend/static/payment_status"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.RefundStateChange', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedRefundStateChange',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'description', type: 'string' }
    ],

    proxy: {
        type: 'ajax',
        url: '{url controller="PaypalUnifiedGeneralSettings" action="getPaymentState"}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
