//{namespace name="backend/paypal_unified_settings/tabs/paypal_plus"}
//{block name="backend/paypal_unified_settings/tabs/paypal_plus"}
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
        labelWidth: '180px'
    },

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [{
            xtype: 'base-element-boolean',
            name: 'plusActive',
            fieldLabel: '{s name=field/activate}Activate PayPal Plus integration{/s}',
            handler: Ext.bind(me.onActivatePayPalPlus, me)
        }, {
            xtype: 'textfield',
            itemId: 'paymentWallLanguage',
            name: 'plusLanguage',
            fieldLabel: '{s name=field/language}Payment Wall language{/s}',
            disabled: true
        }, {
            xtype: 'button',
            cls: 'primary',
            text: '{s name=field/button}Check PayPal Plus{/s}',
            style: {
                float: 'right'
            }
        }];
    },

    /**
     * @param { Shopware.apps.Base.view.element.Boolean } element
     * @param { Boolean } checked
     */
    onActivatePayPalPlus: function (element, checked) {
        var me = this;

        me.down('*[name=plusLanguage]').setDisabled(!checked);
    }
});
//{/block}