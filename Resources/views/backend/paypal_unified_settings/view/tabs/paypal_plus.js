// {namespace name="backend/paypal_unified_settings/tabs/paypal_plus"}
// {block name="backend/paypal_unified_settings/tabs/paypal_plus"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.PaypalPlus', {
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

        me.localeSelection = me.createLocaleSelection();
        me.restyleCheckbox = me.createRestyleCheckbox();

        return [
            {
                xtype: 'checkbox',
                name: 'plusActive',
                fieldLabel: '{s name=field/activate}Activate PayPal Plus integration{/s}',
                boxLabel: '{s name=field/activate/help}Activate to enable the PayPal Plus integration for the selected shop.{/s}',
                inputValue: true,
                uncheckedValue: false,
                handler: Ext.bind(me.onActivatePayPalPlus, me)
            },
            me.restyleCheckbox,
            me.localeSelection
        ];
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createRestyleCheckbox: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'plusRestyle',
            fieldLabel: '{s name=field/restyle}Restyle payment selection{/s}',
            helpText: '{s name=field/restyle/help}Activate this option to automatically apply the payment wall theme to the payment selection.{/s}',
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
            store = Ext.create('Shopware.apps.Base.store.Locale');

        store.filters.clear();
        store.load();

        return Ext.create('Ext.form.field.ComboBox', {
            name: 'plusLanguage',
            store: store,
            fieldLabel: '{s name=field/language}Payment Wall language{/s}',
            helpText: '{s name=field/language/help}You can define another language for the Payment Wall for the selected shop. Leave the selection empty to use the shop locale.{/s}',
            disabled: true,
            queryMode: 'local',
            displayField: 'locale',
            valueField: 'locale',
            forceSelection: false,
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

        me.localeSelection.setDisabled(!checked);
        me.restyleCheckbox.setDisabled(!checked);
    }
});
// {/block}
