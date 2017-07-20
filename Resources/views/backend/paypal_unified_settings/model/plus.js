// {block name="backend/paypal_unified_settings/model/plus"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.Plus', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'PaypalUnifiedPlusSettings'
        };
    },

    fields: [
        // {block name="backend/paypal_unified_settings/model/plus/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'active', type: 'bool' },
        { name: 'restyle', type: 'bool' },
        { name: 'language', type: 'string' }
    ]
});
// {/block}
