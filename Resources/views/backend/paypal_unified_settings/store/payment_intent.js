//{namespace name="backend/paypal_unified_settings/store/payment_intent"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.PaymentIntent', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedPaymentIntent',

    fields: [
        { name: 'id', type: 'int' },
        { name: 'text' }
    ],

    data: [
        { id: 0, text: '{s name="type/sale"}Complete payment immediately (Sale){/s}' },
        { id: 1, text: '{s name="type/authCapture"}Delayed payment collection (Auth-Capture){/s}' },
        { id: 2, text: '{s name="type/orderAuthCapture"}Delayed payment collection (Order-Auth-Capture){/s}' }
    ]
});