// {namespace name="backend/paypal_unified_settings/store/landing_page_type"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.LandingPageType', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedLandingPageType',

    fields: [
        { name: 'type', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { type: 'Login', text: '{s name="login"}Login{/s}' },
        { type: 'Billing', text: '{s name="billing"}Billing{/s}' }
    ]
});
