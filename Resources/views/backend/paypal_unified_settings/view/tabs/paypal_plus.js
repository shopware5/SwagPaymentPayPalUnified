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
            me.localeSelection
        ];
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

        me.localeSelection.setDisabled(!checked);
    }
});
// {/block}
