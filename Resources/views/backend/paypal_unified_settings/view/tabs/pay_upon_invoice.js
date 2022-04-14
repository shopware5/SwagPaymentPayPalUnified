// {namespace name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
// {block name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.PayUponInvoice', {
    extend: 'Shopware.apps.PaypalUnifiedSettings.view.tabs.AbstractPuiAcdcTab',
    alias: 'widget.paypal-unified-settings-tabs-pay-upon-invoice',
    title: '{s name="title"}PayPal Pay Upon Invoice Integration{/s}',

    mixins: [
        'Shopware.apps.PaypalUnified.mixin.OnboardingHelper'
    ],

    buttonValue: 'PAY_UPON_INVOICE',

    snippets: {
        activationFieldset: {
            checkboxFieldLabel: '{s name="fieldset/activation/activate"}Enable for this shop{/s}',
            checkboxLabel: '{s name="fieldset/activation/activate/help"}Enable this option to activate PayPal Pay Upon Invoice for this shop.{/s}'
        },
        settingsFieldset: {
            customerServiceInstructionsLabel: '{s name="fieldset/settings/customer_service_instructions/label"}{/s}',
            placeholder: '{s name="fieldset/settings/customer_service_instructions/placeholder"}{/s}',
            help: '{s name="fieldset/settings/customer_service_instructions/help"}{/s}',
        },
        onboardingPendingMessage: '{s name="onboardingPendingMessage"}Your account is currently not eligible for accepting payments using Pay Upon Invoice.{/s}',
        capabilityTestButtonText: '{s name="button/capability/test"}Capability test{/s}',
        hasLimitsMessage: '{s name="capability/hasLimits/message/pui"}Please go to your <a href="https://www.paypal.com/businessmanage/limits/liftlimits" target="_blank">PayPal Account</a> and clarify which company documents still need to be submitted in order to use Pay Upon Invoice permanent.{/s}',
    },

    activationFieldUseSupportText: true,

    activationFieldSupportText: '{s name="registrationSettingsMessage"}For your customers to be able to pay using this payment method, they will need to provide a phone number as well as their date of birth. Please make sure to activate the corresponding input fields for the registration. (Basic settings - Storefront - Login / Registration){/s}',

    initComponent: function() {
        this.callParent(arguments);

        this.items.insert(
            this.items.indexOf(this.activationFieldSet) + 1,
            this.createSettingsFieldset()
        );

        this.registerEvents()
    },

    registerEvents: function () {
        this.activationField.on('change', Ext.bind(this.onActivationChange, this), this);
    },

    createSettingsFieldset: function() {
        this.settingsFieldSet = Ext.create('Ext.form.FieldSet', {
            items: this.createSettingsFieldsetItems()
        });

        return this.settingsFieldSet;
    },

    handleView: function() {
        this.callParent(arguments);

        if (!this.hasOwnProperty('customerServiceInstructionsField')) {
            return;
        }

        this.customerServiceInstructionsField.setDisabled(true);

        if (this.isOnboardingCompleted() && this.isPaymentMethodActive()) {
            this.customerServiceInstructionsField.setDisabled(false);
        }
    },

    createSettingsFieldsetItems: function() {
        this.customerServiceInstructionsField = Ext.create('Ext.form.field.TextArea', {
            name: 'customerServiceInstructions',
            allowBlank: false,
            disabled: true,
            fieldLabel: this.snippets.settingsFieldset.customerServiceInstructionsLabel,
            emptyText: this.snippets.settingsFieldset.placeholder,
            flex: 1,
            anchor: this.fieldDefaults.anchor,
            labelWidth: this.fieldDefaults.labelWidth,
            helpText: this.snippets.settingsFieldset.help,
        });

        return [
            this.customerServiceInstructionsField
        ];
    },

    onActivationChange: function() {
        this.handleView();
    },
});
// {/block}
