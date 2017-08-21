// {namespace name="backend/paypal_unified_settings/store/plus_language"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.PlusLanguage', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedPlusLanguage',

    fields: [
        { name: 'iso', type: 'string' },
        { name: 'language', type: 'string' }
    ],

    data: [
        { iso: 'en_GB', language: '{s name="language/english"}English{/s}' },
        { iso: 'de_DE', language: '{s name="language/german"}German{/s}' }
    ]
});