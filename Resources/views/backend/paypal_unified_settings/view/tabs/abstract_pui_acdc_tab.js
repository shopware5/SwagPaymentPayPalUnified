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
     * @type { Ext.container.Container }
     */
    hasLimitsMessage: null,

    /**
     * @type { Boolean }
     */
    hasLimits: false,

    /**
     * @type { String }
     */
    hasLimitsIcon: 'sprite-exclamation paypal-has-limits-icon',

    /**
     * @type { String }
     */
    iconClass: 'paypal-has-limits-icon',

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
            this.createHasLimitsMessage(),
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

    createHasLimitsMessage: function() {
        this.hasLimitsMessage = Shopware.Notification.createBlockMessage(
            this.snippets.hasLimitsMessage,
            'alert'
        );

        return this.hasLimitsMessage;
    },

    createActivationFieldset: function() {
        this.activationFieldSet = Ext.create('Ext.form.FieldSet', {
            items: this.createActivationFieldsetItems(),
            disabled: true
        });

        return this.activationFieldSet;
    },

    createActivationFieldsetItems: function() {
        var me = this,
            fieldConfig = {
                name: 'active',
                fieldLabel: me.snippets.activationFieldset.checkboxFieldLabel,
                boxLabel: me.snippets.activationFieldset.checkboxLabel,
                inputValue: true,
                uncheckedValue: false
            };

        this.activationField = Ext.create('Ext.form.field.Checkbox', fieldConfig);

        return [
            this.activationField
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
        this.hasLimitsMessage.hide();
        this.setIconCls(null);

        if (this.isOnboardingCompleted()) {
            this.onboardingMessage.hide();
            this.activationFieldSet.setDisabled(false);
            this.onboardingFieldset.hide();
            this.capabilityTestButton.show();
        }

        if (!this.hasLimits || this.getSandbox() || !this.getForm().getRecord().get('active')) {
            return;
        }

        this.showHasLimits();
    },

    showHasLimits: function () {
        this.setIconCls(this.hasLimitsIcon);
        this.adjustIconHeight();
        this.hasLimitsMessage.show();
    },

    adjustIconHeight: function() {
        var icons = document.getElementsByClassName(this.iconClass);

        Ext.each(icons, function(icon) {
            icon.style.height = '16px';
        });
    },

    isPaymentMethodActive: function() {
        return this.activationField.getValue();
    },

    setHasLimits: function(hasLimits) {
        this.hasLimits = hasLimits;

        this.handleView();
    },
});
// {/block}
