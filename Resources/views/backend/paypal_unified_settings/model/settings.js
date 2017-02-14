//{block name="backend/paypal_unified_settings/model/settings"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.Settings', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'PaypalUnifiedSettings'
        }
    },

    fields: [
        //{block name="backend/paypal_unified_settings/model/settings/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'clientId' },
        { name: 'clientSecret' },
        { name: 'sandbox', type: 'bool' },
        { name: 'showSidebarLogo', type: 'bool' },
        { name: 'brandName' },
        { name: 'logoImage' },
        { name: 'sendOrderNumber', type: 'bool' },
        { name: 'orderNumberPrefix' },
        { name: 'plusActive', type: 'bool' },
        { name: 'plusLanguage' }
    ]
});
//{/block}