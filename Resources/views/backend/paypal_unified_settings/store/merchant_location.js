// {namespace name="backend/paypal_unified_settings/store/merchant_location"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.MerchantLocation', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedMerchantLocation',

    fields: [
        { name: 'type', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { type: 'germany', text: '{s name="germany"}Germany{/s}' },
        { type: 'other', text: '{s name="other"}Other merchant location{/s}' }
    ]
});
