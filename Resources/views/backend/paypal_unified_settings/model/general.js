// {block name="backend/paypal_unified_settings/model/general"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.General', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'PaypalUnifiedGeneralSettings'
        };
    },

    fields: [
        // {block name="backend/paypal_unified_settings/model/general/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'active', type: 'bool' },
        { name: 'clientId', type: 'string' },
        { name: 'clientSecret', type: 'string' },
        { name: 'sandbox', type: 'bool' },
        { name: 'showSidebarLogo', type: 'bool' },
        { name: 'brandName', type: 'string' },
        { name: 'sendOrderNumber', type: 'bool' },
        { name: 'orderNumberPrefix', type: 'string' },
        { name: 'useInContext', type: 'bool', defaultValue: true },
        { name: 'landingPageType', type: 'string', defaultValue: 'Login' },
        { name: 'logLevel', type: 'int', defaultValue: 0 },
        { name: 'displayErrors', type: 'bool' },
        { name: 'advertiseReturns', type: 'bool' },
        { name: 'merchantLocation', type: 'string', defaultValue: 'germany' },
        { name: 'useSmartPaymentButtons', type: 'bool' },
        { name: 'submitCart', type: 'bool', defaultValue: true }
    ]
});
// {/block}
