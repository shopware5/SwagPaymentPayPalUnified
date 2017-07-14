// {namespace name="backend/paypal_unified_settings/store/ec_button_style_shape"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleShape', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedEcButtonStyleShape',

    fields: [
        { name: 'id', type: 'string' },
        { name: 'text', type: 'string' }
    ],

    data: [
        { id: 'pill', text: '{s name="shape/pill"}Pill{/s}' },
        { id: 'rect', text: '{s name="shape/rect"}Rect{/s}' }
    ]
});
