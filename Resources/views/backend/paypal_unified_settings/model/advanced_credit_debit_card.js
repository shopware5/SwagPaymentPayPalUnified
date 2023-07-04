// {block name="backend/paypal_unified_settings/model/advanced_credit_debit_card"}
// {namespace name="backend/paypal_unified_settings/model/advanced_credit_debit_card"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.model.AdvancedCreditDebitCard', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'PaypalUnifiedAdvancedCreditDebitCardSettings'
        };
    },

    fields: [
        // {block name="backend/paypal_unified_settings/model/advanced_credit_debit_card/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'shopId', type: 'int' },
        { name: 'onboardingCompleted', type: 'bool', defaultValue: false },
        { name: 'sandboxOnboardingCompleted', type: 'bool', defaultValue: false },
        { name: 'blockCardsFromNonThreeDsCountries', type: 'bool', defaultValue: false },
        { name: 'active', type: 'bool', defaultValue: false }
    ]
});
// {/block}
