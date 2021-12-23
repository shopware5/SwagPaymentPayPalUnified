// {block name="backend/paypal_unified_settings/model/pay_upon_invoice"}
// {namespace name="backend/paypal_unified_settings/model/pay_upon_invoice"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.PayUponInvoice', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'PaypalUnifiedPayUponInvoiceSettings'
        };
    },

    fields: [
        // {block name="backend/paypal_unified_settings/model/pay_upon_invoice/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'onboardingCompleted', type: 'bool', defaultValue: false },
        { name: 'sandboxOnboardingCompleted', type: 'bool', defaultValue: false },
        { name: 'active', type: 'bool', defaultValue: false }
    ]
});
// {/block}
