// {block name="backend/paypal_unified_settings/model/installments"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.Installments', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'PaypalUnifiedInstallmentsSettings'
        };
    },

    fields: [
        // {block name="backend/paypal_unified_settings/model/installments/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'active', type: 'bool' },
        { name: 'presentmentTypeDetail', type: 'int' },
        { name: 'presentmentTypeCart', type: 'int' },
        { name: 'showLogo', type: 'bool' },
        { name: 'intent', type: 'int' }
    ]
});
// {/block}
