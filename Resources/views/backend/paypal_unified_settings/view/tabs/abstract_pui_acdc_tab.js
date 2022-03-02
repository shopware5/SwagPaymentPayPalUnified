// {namespace name="backend/paypal_unified_settings/tabs/abstract_pui_acdc_tab"}
// {block name="backend/paypal_unified_settings/tabs/abstract_pui_acdc_tab"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.AbstractPuiAcdcTab', {
    extend: 'Ext.form.Panel',

    anchor: '100%',
    bodyPadding: 10,
    border: false,

    style: {
        background: '#EBEDEF'
    },

    fieldDefaults: {
        anchor: '100%',
        labelWidth: 180
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

        this.handleView();
    },

    createItems: function() {
        return [
            this.createHiddenNumberField('id'),
            this.createHiddenNumberField('shopId'),
            this.createHiddenCheckboxField('onboardingCompleted'),
            this.createHiddenCheckboxField('sandboxOnboardingCompleted'),
            this.createActivationFieldset(),
            this.createOnboardingMessage(),
            this.createOnboardingFieldset(),
            this.createCapabilityTestButton(),
        ]
    },

    createHiddenNumberField: function(name) {
        var selfPropertyName = name + 'Field';

        this[selfPropertyName] = Ext.create('Ext.form.field.Number', {
            name: name,
            hidden: true
        });

        return this[selfPropertyName];
    },

    createHiddenCheckboxField: function(name) {
        var selfPropertyName = name + 'Field';

        this[selfPropertyName] = Ext.create('Ext.form.field.Checkbox', {
            name: name,
            inputValue: true,
            uncheckedValue: false,
            hidden: true
        });

        return this[selfPropertyName];
    },

    createOnboardingMessage: function() {
        this.onboardingMessage = Shopware.Notification.createBlockMessage(
            this.snippets.onboardingPendingMessage,
            'alert'
        );

        return this.onboardingMessage;
    },

    createActivationFieldset: function() {
        this.activationFieldSet = Ext.create('Ext.form.FieldSet', {
            items: this.createActivationFieldsetItems(),
            disabled: true
        });

        return this.activationFieldSet;
    },

    createActivationFieldsetItems: function() {
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

    createOnboardingFieldset: function() {
        this.onboardingFieldset = Ext.create('Ext.form.FieldSet', {
            items: this.createOnboardingFieldsetItems(),
        });

        return this.onboardingFieldset;
    },

    createOnboardingFieldsetItems: function() {
        return [
            this.createOnboardingButtonFormElement(this.buttonValue)
        ];
    },

    createCapabilityTestButton: function() {
        this.capabilityTestButton = Ext.create('Ext.button.Button', {
            text: this.snippets.capabilityTestButtonText,
            name: this.buttonValue,
            cls: 'primary',
            ui: 'shopware-ui',
            handler: Ext.bind(this.onCapabilityTestButtonClick, this)
        });

        return this.capabilityTestButton;
    },

    onCapabilityTestButtonClick: function() {
        this.fireEvent('onTestCapability', this.capabilityTestButton);
    },

    isOnboardingCompleted: function() {
        return this.getForm() &&
            this.getForm().getRecord() &&
            this.getForm().getRecord().get(this.getSandbox() ? 'sandboxOnboardingCompleted' : 'onboardingCompleted');
    },

    handleView: function() {
        this.onboardingMessage.show();
        this.activationFieldSet.setDisabled(true);
        this.onboardingFieldset.show();
        this.capabilityTestButton.hide();

        if (this.isOnboardingCompleted()) {
            this.onboardingMessage.hide();
            this.activationFieldSet.setDisabled(false);
            this.onboardingFieldset.hide();
            this.capabilityTestButton.show();
        }
    },
});
// {/block}
