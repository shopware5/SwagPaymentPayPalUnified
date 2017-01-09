//{namespace name="backend/paypal_unified/sidebar/order/details"}
//{block name="backend/paypal_unified/sidebar/order/details"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.order.Details', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-order-details',
    title: '{s name=title}Order details{/s}',

    anchor: '100%',
    bodyPadding: 5,
    margin: 5,

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
            name: 'number',
            fieldLabel: '{s name=field/number}Order number{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'transactionId',
            fieldLabel: '{s name=field/transactionId}Transaction ID{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'currency',
            fieldLabel: '{s name=field/currency}Currency{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'invoiceAmount',
            itemId: 'invoiceAmount',
            fieldLabel: '{s name=field/invoiceAmount}Invoice amount{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'base-element-datetime',
            name: 'orderTime',
            fieldLabel: '{s name=field/orderTime}Order time{/s}',
            anchor: '100%',
            dateCfg: {
                readOnly: true
            },
            timeCfg: {
                readOnly: true
            }
        }, {
            xtype: 'textfield',
            name: 'orderStatus',
            itemId: 'orderStatus',
            fieldLabel: '{s name=field/orderStatus}Order status{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'paymentStatus',
            itemId: 'paymentStatus',
            fieldLabel: '{s name=field/paymentStatus}Payment status{/s}',
            readOnly: true,
            anchor: '100%'
        }]
    }
});
//{/block}