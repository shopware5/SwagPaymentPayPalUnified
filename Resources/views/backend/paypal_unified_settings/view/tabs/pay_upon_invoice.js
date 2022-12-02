// {namespace name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
// {block name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.PayUponInvoice', {
    extend: 'Shopware.apps.PaypalUnifiedSettings.view.tabs.AbstractPuiAcdcTab',
    alias: 'widget.paypal-unified-settings-tabs-pay-upon-invoice',
    title: '{s name="new/title"}Pay Upon Invoice Integration{/s}',

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
        showRatePayHintInMailField: {
            fieldLabel: '{s name="showRatePayHintInMailField/fieldLabel"}Show hint to RatePay invoice under payment method{/s}',
            boxLabel: '{s name="showRatePayHintInMailField/boxLabel"}This field should stay active. Alternatively, customise your EmailFooter under "Basic Settings - Email Settings" with the code below.{/s}',
        }
    },

    initComponent: function() {
        this.callParent(arguments);

        this.items.insert(
            this.items.indexOf(this.activationFieldSet) + 1,
            this.createSettingsFieldset()
        );

        this.items.insert(
            0,
            this.createMoreInformationNotice()
        )

        this.registerEvents()
    },

    registerEvents: function() {
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
        this.showRatePayHintInMailField = Ext.create('Ext.form.field.Checkbox', {
            name: 'showRatePayHintInMail',
            fieldLabel: this.snippets.showRatePayHintInMailField.fieldLabel,
            boxLabel: this.snippets.showRatePayHintInMailField.boxLabel,
            supportText: [
                '{literal}',
                '{if $additional.paypalUnifiedRatePayHint}',
                '&nbsp;&nbsp;&nbsp;&nbsp;{$additional.paypalUnifiedRatePayHint}',
                '{/if}',
                '{/literal}',
            ].join('<br>'),
            inputValue: true,
            uncheckedValue: false,
            labelWidth: 180,
        });

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
            this.customerServiceInstructionsField,
            this.showRatePayHintInMailField
        ];
    },

    onActivationChange: function() {
        this.handleView();
    },

    createMoreInformationNotice: function() {
        var noticeText = '{s name="moreInformationText"}Worth knowing and details about Pay Upon Invoice you can find <a href="https://www.paypal.com/de/rechnungskauf-information" target="_blank">here</a>.{/s}',
            noticeStyle = {
                'color': 'white',
                'font-size': '14px',
                'background-color': '#4AA3DF',
                'text-shadow': '0 0 5px rgba(0, 0, 0, 0.3)'
            },
            notice = Shopware.Notification.createBlockMessage(noticeText, 'info');
        notice.style = noticeStyle;

        this.noticeContainer = Ext.create('Ext.container.Container', {
            items: [notice]
        });

        return this.noticeContainer;
    }
});
// {/block}
