// {namespace name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
// {block name="backend/paypal_unified_settings/tabs/paypal_pay_upon_invoice"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.PayUponInvoice', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-pay-upon-invoice',
    title: '{s name="title"}PayPal Pay Upon Invoice Integration{/s}',

    mixins: [
        'Shopware.apps.PaypalUnified.mixin.OnboardingHelper'
    ],

    snippets: {
        activationFieldset: {
            checkboxFieldLabel: '{s name="fieldset/activation/activate"}Enable for this shop{/s}',
            checkboxLabel: '{s name="fieldset/activation/activate/help"}Enable this option to activate PayPal Pay Upon Invoice for this shop.{/s}'
        },
        onboardingPendingMessage: '{s name="onboardingPendingMessage"}Your account is currenctly not eligible for accepting payments using Pay Upon Invoice.{/s}',
    },

    anchor: '100%',
    bodyPadding: 10,
    border: false,

    style: {
        background: '#EBEDEF'
    },

    fieldDefaults: {
        anchor: '100%',
        labelWidth: 250
    },

    config: {
        authCodeReceivedEventName: 'authCodeReceived'
    },

    /**
     * @type { Ext.container.Container }
     */
    onboardingMessage: null,

    /**
     * @type { Ext.button.Button }
     */
    onboardingButton: null,

    initComponent: function() {
        this.addEvents(this.getAuthCodeReceivedEventName());

        this.items = this.createItems();

        this.callParent(arguments);
    },

    createItems: function () {
        var items = [];

        if (!this.isOnboardingCompleted()) {
            items.push(this.createOnboardingMessage());
        }

        items.push(this.createActivationFieldset());
        items.push(this.createOnboardingFieldset());

        return items;
    },

    createOnboardingMessage: function () {
        this.onboardingMessage = Shopware.Notification.createBlockMessage(
            this.snippets.onboardingPendingMessage,
            'alert'
        );

        return this.onboardingMessage;
    },

    createActivationFieldset: function () {
        this.activationFieldSet = Ext.create('Ext.form.FieldSet', {
            items: this.createActivationFieldsetItems(),
            disabled: !this.isOnboardingCompleted()
        });

        return this.activationFieldSet;
    },

    createActivationFieldsetItems: function () {
        var me = this;

        return [
            {
                xtype: 'checkbox',
                name: 'active',
                fieldLabel: me.snippets.activationFieldset.checkboxFieldLabel,
                boxLabel: me.snippets.activationFieldset.checkboxLabel,
                inputValue: true,
                uncheckedValue: false
            },
        ];
    },

    createOnboardingFieldset: function () {
        this.onboardingFieldset = Ext.create('Ext.form.FieldSet', {
            items: this.createOnboardingFieldsetItems(),
            hidden: this.isOnboardingCompleted()
        });

        return this.onboardingFieldset;
    },

    createOnboardingFieldsetItems: function () {
        return [
            this.createOnboardingButtonFormElement()
        ];
    },

    isOnboardingCompleted: function () {
        return this.getForm() &&
            this.getForm().getRecord() &&
            this.getForm().getRecord().get(this.getSandbox() ? 'sandboxOnboardingCompleted' : 'onboardingCompleted');
    },

    refreshTabItems: function () {
        this.removeAll();
        this.add(this.createItems());
    }
});
// {/block}
