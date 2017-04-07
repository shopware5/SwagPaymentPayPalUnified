// {namespace name="backend/paypal_unified_settings/store/installments_presentment"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.InstallmentsPresentment', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedInstallmentsPresentment',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { id: 0, text: '{s name="display/none"}None{/s}' },
        { id: 1, text: '{s name="display/simple"}Simple{/s}' },
        { id: 2, text: '{s name="display/cheapest"}Cheapest rate{/s}' }
    ]
});
