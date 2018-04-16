// {namespace name="backend/paypal_unified_settings/tabs/paypal_plus"}
// {block name="backend/paypal_unified_settings/tabs/paypal_plus"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.Plus', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-paypal-plus',
    title: '{s name=title}PayPal Plus Integration{/s}',

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

    /**
     * @type { Ext.form.field.ComboBox }
     */
    intentSelection: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    restyleCheckbox: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    integrateThirdPartyMethodsCheckbox: null,

    /**
     * @type { Ext.form.field.Text }
     */
    paymentNameField: null,

    /**
     * @type { Ext.form.field.Text }
     */
    paymentDescriptionField: null,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this;

        me.intentSelection = me.createPaymentIntentSelection();
        me.restyleCheckbox = me.createRestyleCheckbox();
        me.integrateThirdPartyMethodsCheckbox = me.createIntegrateThirdPartyMethodsCheckbox();
        me.paymentNameField = me.createPaymentNameField();
        me.paymentDescriptionField = me.createPaymentDescriptionField();

        return [
            me.createNotice(),
            {
                xtype: 'checkbox',
                name: 'active',
                fieldLabel: '{s name=field/activate}Activate PayPal Plus{/s}',
                boxLabel: '{s name=field/activate/help}Activate in order to enable the PayPal Plus integration for the selected shop.{/s}',
                inputValue: true,
                uncheckedValue: false,
                handler: Ext.bind(me.onActivatePayPalPlus, me)
            },
            me.intentSelection,
            me.restyleCheckbox,
            me.integrateThirdPartyMethodsCheckbox,
            me.paymentNameField,
            me.paymentDescriptionField
        ];
    },

    /**
     * @returns { Ext.form.Container }
     */
    createNotice: function () {
        var infoNotice = Shopware.Notification.createBlockMessage('{s name=description}PayPal Plus - the four most popular payment methods of German buyers: PayPal, direct debit, credit card and invoice! <br> You can get PayPal Plus here: <a href="https://www.paypal.de/plus" title="https://www.paypal.de/plus" target="_blank">https://www.paypal.de/plus</a>{/s}', 'info');

        //There is no style defined for the type "info" in the shopware backend stylesheet, therefore we have to apply it manually
        infoNotice.style = {
            'color': 'white',
            'font-size': '14px',
            'background-color': '#4AA3DF',
            'text-shadow': '0 0 5px rgba(0, 0, 0, 0.3)'
        };

        return infoNotice;
    },

    /**
     * @return { Ext.form.field.ComboBox }
     */
    createPaymentIntentSelection: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'intent',
            fieldLabel: '{s name="intent/field" namespace="backend/paypal_unified_settings/tabs/payment_intent"}{/s}',
            helpText: '',

            store: {
                fields: [
                    { name: 'id', type: 'int' },
                    { name: 'text', type: 'string' }
                ],

                data: [
                    { id: 0, text: '{s name="intent/sale" namespace="backend/paypal_unified_settings/tabs/payment_intent"}Complete payment immediately (Sale){/s}' }
                ]
            },

            valueField: 'id',
            disabled: true,
            value: 0
        });
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createRestyleCheckbox: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'restyle',
            fieldLabel: '{s name=field/restyle}Restyle payment selection{/s}',
            helpText: '{s name=field/restyle/help}Activate this option to apply the payment wall style to the whole payment selection.{/s}',
            boxLabel: '{s name=field/restyle/boxLabel}Activate this option to restyle the payment selection.{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true,
            handler: Ext.bind(this.onActivateRestyle, this)
        });
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createIntegrateThirdPartyMethodsCheckbox: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'integrateThirdPartyMethods',
            fieldLabel: '{s name=field/integrateThirdPartyMethods}Display other payments methods in iFrame{/s}',
            helpText: "{s name=field/integrateThirdPartyMethods/help}Activate this option to display third party payment methods in the Payment Wall iFrame. Select the payment method you want to display there in 'Configuration' -> 'Payment methods' -> 'Free text fields'{/s}",
            boxLabel: '{s name=field/integrateThirdPartyMethods/boxLabel}Activate this option to display third party methods in the Payment Wall iFrame.{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true
        });
    },

    /**
     * @return { Ext.form.field.Text }
     */
    createPaymentNameField: function() {
        return Ext.create('Ext.form.field.Text', {
            name: 'paymentName',
            fieldLabel: '{s name=field/paymentName}Overwrite payment method name{/s}',
            helpText: '{s name=field/paymentName/help}With this setting you are able to overwrite the payment method name of PayPal.<br>Note: this will only have affect in the storefront view.{/s}',
            disabled: true
        });
    },

    /**
     * @return { Ext.form.field.Text }
     */
    createPaymentDescriptionField: function() {
        return Ext.create('Ext.form.field.Text', {
            name: 'paymentDescription',
            fieldLabel: '{s name=field/paymentDescription}{/s}',
            helpText: '{s name=field/paymentDescription/help}{/s}',
            disabled: true
        });
    },

    /**
     * @param { Ext.form.field.Checkbox } element
     * @param { Boolean } checked
     */
    onActivatePayPalPlus: function(element, checked) {
        var me = this;

        // A little trick to set the „default“ value of this field.
        // Otherwise a default value would not be possible, since the data of the record
        // would be applied.
        if (checked) {
            me.restyleCheckbox.setValue(true);
        }

        me.intentSelection.setDisabled(!checked);
        me.restyleCheckbox.setDisabled(!checked);
        me.integrateThirdPartyMethodsCheckbox.setDisabled(!checked);
        me.paymentNameField.setDisabled(!checked);
        me.paymentDescriptionField.setDisabled(!checked);
    },

    /**
     *
     * @param { Ext.form.field.Checkbox } element
     * @param { Boolean } checked
     */
    onActivateRestyle: function(element, checked) {
        var me = this;

        me.integrateThirdPartyMethodsCheckbox.setVisible(checked);

        if (!checked) {
            me.integrateThirdPartyMethodsCheckbox.setValue(false);
        }
    }
});
// {/block}
