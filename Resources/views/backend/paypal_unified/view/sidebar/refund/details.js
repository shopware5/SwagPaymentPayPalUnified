//{namespace name="backend/paypal_unified/sidebar/refund/details"}
//{block name="backend/paypal_unified/sidebar/refund/details"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.refund.Details', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-refund-details',
    title: '{s name=title}Details{/s}',

    anchor: '100%',
    bodyPadding: 5,
    margin: 5,
    disabled: true,
    fieldDefaults: {
        anchor: '100%',
        readOnly: true
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
            xtype: 'textfield',
            name: 'id',
            fieldLabel: '{s name="field/id"}Sale ID{/s}'
        }, {
            xtype: 'textfield',
            name: 'invoice_number',
            fieldLabel: '{s name="field/bookingNumber"}Booking number{/s}'
        }, {
            xtype: 'textfield',
            name: 'state',
            fieldLabel: '{s name="field/state"}State{/s}'
        }, {
            xtype: 'textfield',
            name: 'total',
            itemId: 'totalAmount',
            fieldLabel: '{s name="field/amount"}Amount{/s}'
        }, {
            xtype: 'textfield',
            name: 'value',
            itemId: 'transactionFee',
            fieldLabel: '{s name="field/transactionFee"}Transaction fee{/s}'
        }, {
            xtype: 'textfield',
            name: 'payment_mode',
            itemId: 'paymentMode',
            fieldLabel: '{s name="field/paymentMode"}Mode{/s}'
        }, {
            xtype: 'textfield',
            name: 'create_time',
            itemId: 'createTime',
            fieldLabel: '{s name="field/createTime"}Create time{/s}'
        }, {
            xtype: 'textfield',
            name: 'update_time',
            itemId: 'updateTime',
            fieldLabel: '{s name="field/updateTime"}Update time{/s}'
        }];
    }
});
//{/block}