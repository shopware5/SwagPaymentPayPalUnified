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
        { name: 'active', type: 'bool' },
        { name: 'detailActive', type: 'bool' },
        { name: 'buttonStyleColor', type: 'string' },
        { name: 'buttonStyleShape', type: 'string' },
        { name: 'buttonStyleSize', type: 'string' },
        { name: 'submitCart', type: 'bool' }
    ]
});
// {/block}
