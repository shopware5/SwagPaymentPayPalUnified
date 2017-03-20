//{namespace name="backend/paypal_unified_settings/tabs/paypal"}
//{block name="backend/paypal_unified_settings/tabs/paypal"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.Paypal', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-paypal',
    title: '{s name=title}PayPal Integration{/s}',

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
        return [{
            xtype: 'combobox',
            name: 'paypalPaymentIntent',
            fieldLabel: '{s name=field/paymentIntent}Payment acquisition{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.PaymentIntent'),
            valueField: 'id',
            value: 0,
            supportText: '{s name=field/paymentIntent/support}*This option does not have any effect if the order has been payed with PayPal Plus{/s}',
        }];
    }
});
//{/block}