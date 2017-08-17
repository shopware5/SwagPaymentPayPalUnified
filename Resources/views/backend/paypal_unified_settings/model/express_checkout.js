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
        { name: 'detailActive', type: 'bool' },
        { name: 'cartActive', type: 'bool' },
        { name: 'buttonStyleColor', type: 'string', defaultValue: 'gold' },
        { name: 'buttonStyleShape', type: 'string', defaultValue: 'pill' },
        { name: 'buttonStyleSize', type: 'string', defaultValue: 'small' },
        { name: 'submitCart', type: 'bool' },
        { name: 'intent', type: 'int' }
    ]
});
// {/block}
