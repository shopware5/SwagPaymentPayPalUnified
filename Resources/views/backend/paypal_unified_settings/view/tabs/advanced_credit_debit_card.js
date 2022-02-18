// {namespace name="backend/paypal_unified_settings/tabs/advanced_credit_debit_card"}
// {block name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.AdvancedCreditDebitCard', {
    extend: 'Shopware.apps.PaypalUnifiedSettings.view.tabs.AbstractPuiAcdcTab',
    alias: 'widget.paypal-unified-settings-tabs-advanced-credit-debit-card',
    title: '{s name="title"}PayPal Advanced Credit Debit Card Integration{/s}',

    mixins: [
        'Shopware.apps.PaypalUnified.mixin.OnboardingHelper'
    ],

    buttonValue: 'CUSTOM_CARD_PROCESSING',

    snippets: {
        activationFieldset: {
            checkboxFieldLabel: '{s name="fieldset/activation/activate"}Enable for this shop{/s}',
            checkboxLabel: '{s name="fieldset/activation/activate/help"}Enable this option to activate PayPal Advanced Credit Debit Card for this shop.{/s}'
        },
        onboardingPendingMessage: '{s name="onboardingPendingMessage"}Your account is currently not eligible for accepting payments using Advanced Credit Debit Card.{/s}',
        capabilityTestButtonText: '{s name="button/capability/test"}Capability test{/s}'
    },
});
// {/block}
