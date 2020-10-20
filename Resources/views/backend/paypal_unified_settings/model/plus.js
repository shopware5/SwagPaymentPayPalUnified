// {block name="backend/paypal_unified_settings/model/plus"}
// {namespace name="backend/paypal_unified_settings/model/plus"}
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
        { name: 'integrateThirdPartyMethods', type: 'bool' },
        { name: 'paymentName', type: 'string', defaultValue: '{s name="field/paymentName/default"}PayPal, debit advice or credit card{/s}' },
        { name: 'paymentDescription', type: 'string', defaultValue: '{s name="field/paymentDescription/default"}Paying with debit advice or credit card is possible without PayPal account{/s}' }

        // Note: we don't need any intent here, because this payment method does only support "sale". The field exists anyway but only "sale" is being displayed.
    ]
});
// {/block}
