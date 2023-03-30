// {block name="backend/paypal_unified_settings/model/express_checkout"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'PaypalUnifiedExpressSettings'
        };
    },

    fields: [
        // {block name="backend/paypal_unified_settings/model/express_checkout/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'detailActive', type: 'bool', defaultValue: true },
        { name: 'cartActive', type: 'bool', defaultValue: true },
        { name: 'offCanvasActive', type: 'bool', defaultValue: true },
        { name: 'loginActive', type: 'bool', defaultValue: true },
        { name: 'listingActive', type: 'bool', defaultValue: false },
        { name: 'buttonStyleColor', type: 'string', defaultValue: 'gold' },
        { name: 'buttonStyleShape', type: 'string', defaultValue: 'rect' },
        { name: 'buttonStyleSize', type: 'string', defaultValue: 'responsive' },
        { name: 'buttonLocale', type: 'string', defaultValue: '' },
        { name: 'submitCart', type: 'bool' },
        { name: 'intent', type: 'int' }
    ]
});
// {/block}
