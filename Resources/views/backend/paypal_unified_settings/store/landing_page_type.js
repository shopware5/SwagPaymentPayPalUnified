// {namespace name="backend/paypal_unified_settings/store/landing_page_type"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.store.LandingPageType', {
    extend: 'Ext.data.Store',

    storeId: 'SwagPaymentPayPalUnifiedLandingPageType',

    fields: [
        { name: 'key', type: 'string' },
        { name: 'label', type: 'string' },
        { name: 'description', type: 'string' },
    ],

    data: [
        {
            key: 'NO_PREFERENCE',
            label: '{s name="landingPageSelectOption/noPreferences/label"}No preference (recommended){/s}',
            description: '{s name="landingPageSelectOption/noPreferences/text"}When the customer clicks on PayPal Checkout, the customer is redirected to either a page to log in to PayPal and approve the payment or to a page to enter credit or debit card and other relevant billing information required to complete the purchase, depending on their previous interaction with PayPal.{/s}'
        },
        {
            key: 'LOGIN',
            label: '{s name="landingPageSelectOption/login/label"}Login{/s}',
            description: '{s name="landingPageSelectOption/login/text"}When the customer clicks on PayPal Checkout, the customer is redirected to a page to log in to PayPal and approve the payment.{/s}'
        },
        {
            key: 'BILLING',
            label: '{s name="landingPageSelectOption/billing/label"}Billing{/s}',
            description: '{s name="landingPageSelectOption/billing/text"}When the customer clicks on PayPal Checkout, the customer is redirected to a page to enter credit or debit card and other relevant billing information required to complete the purchase.{/s}'
        }
    ]
});
