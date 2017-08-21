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
     * @type { Ext.form.field.ComboBox }
     */
    localeSelection: null,

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
        me.localeSelection = me.createLocaleSelection();
        me.restyleCheckbox = me.createRestyleCheckbox();

        return [
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
            me.localeSelection
        ];
    },

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
     * @returns { Ext.form.field.Checkbox }
     */
    createRestyleCheckbox: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'restyle',
            fieldLabel: '{s name=field/restyle}Restyle payment selection{/s}',
            helpText: '{s name=field/restyle/help}Activate this option to apply the payment wall style to the whole payment selection.{/s}',
            boxLabel: '{s name=field/restyle/boxLabel}Activate this option to restyle the payment selection.{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createLocaleSelection: function() {
        var me = this,
            store = Ext.create('Shopware.apps.PaypalUnifiedSettings.store.PlusLanguage');

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'language',
            store: store,
            fieldLabel: '{s name=field/language}Payment Wall language{/s}',
            helpText: '{s name=field/language/help}You can define another language for the Payment Wall for the selected shop. Leave the selection empty in order to use the shop locale.{/s}',
            disabled: true,
            displayField: 'language',
            valueField: 'iso',
            editable: false,
            emptyText: '{s name=field/language/emptyText}Use the shop language{/s}',
            listeners: {
                change: function(combo, newValue) {
                    if (newValue === null) {
                        me.getRecord().set('plusLanguage', null);
                    }
                }
            }
        });
    },

    /**
     * @param { Shopware.apps.Base.view.element.Boolean } element
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
        me.localeSelection.setDisabled(!checked);
        me.restyleCheckbox.setDisabled(!checked);
    }
});
// {/block}
