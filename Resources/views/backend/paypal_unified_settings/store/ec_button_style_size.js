// {namespace name="backend/paypal_unified_settings/store/ec_button_style_size"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleSize', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedEcButtonStyleSize',

    fields: [
        { name: 'id', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { id: 'small', text: '{s name="size/small"}Small{/s}' },
        { id: 'medium', text: '{s name="size/medium"}Medium{/s}' },
        { id: 'large', text: '{s name="size/large"}Large{/s}' },
        { id: 'responsive', text: '{s name="size/responsive"}Responsive{/s}' }
    ]
});
