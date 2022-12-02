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
        { name: 'advertiseInstallments', type: 'bool', defaultValue: true },
        { name: 'showPayLaterPaypal', type: 'bool', defaultValue: true },
        { name: 'showPayLaterExpress', type: 'bool', defaultValue: true }
    ]
});
// {/block}
