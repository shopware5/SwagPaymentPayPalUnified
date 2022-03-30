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
        { name: 'paypalPayerId', type: 'string', defaultValue: '' },
        { name: 'sandboxClientId', type: 'string' },
        { name: 'sandboxClientSecret', type: 'string' },
        { name: 'sandboxPaypalPayerId', type: 'string', defaultValue: '' },
        { name: 'sandbox', type: 'bool' },
        { name: 'showSidebarLogo', type: 'bool' },
        { name: 'brandName', type: 'string' },
        { name: 'sendOrderNumber', type: 'bool' },
        { name: 'orderNumberPrefix', type: 'string' },
        { name: 'useInContext', type: 'bool', defaultValue: true },
        { name: 'landingPageType', type: 'string', defaultValue: 'NO_PREFERENCE' },
        { name: 'displayErrors', type: 'bool' },
        { name: 'useSmartPaymentButtons', type: 'bool' },
        { name: 'submitCart', type: 'bool', defaultValue: true },
        { name: 'intent', type: 'string', defaultValue: 'CAPTURE' },
        { name: 'buttonStyleColor', type: 'string', defaultValue: 'gold' },
        { name: 'buttonStyleShape', type: 'string', defaultValue: 'rect' },
        { name: 'buttonStyleSize', type: 'string', defaultValue: 'large' },
        { name: 'buttonLocale', type: 'string', defaultValue: '' }
    ]
});
// {/block}
