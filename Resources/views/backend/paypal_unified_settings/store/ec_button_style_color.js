// {namespace name="backend/paypal_unified_settings/store/ec_button_style_color"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleColor', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedEcButtonStyleColor',

    fields: [
        { name: 'id', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { id: 'gold', text: '{s name="color/gold"}Gold{/s}' },
        { id: 'blue', text: '{s name="color/blue"}Blue{/s}' },
        { id: 'silver', text: '{s name="color/silver"}Silver{/s}' }
    ]
});
