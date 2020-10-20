// {namespace name="backend/paypal_unified/sidebar/order/details"}
// {block name="backend/paypal_unified/sidebar/order/details"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.order.Details', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.paypal-unified-sidebar-order-details',
    title: '{s name="title"}Order details{/s}',

    anchor: '100%',
    margin: 5,

    defaults: {
        anchor: '100%',
        labelWidth: 130,
        readOnly: true
    },

    style: {
        background: '#EBEDEF'
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
            name: 'number',
            fieldLabel: '{s name="field/number"}Order number{/s}'
        }, {
            xtype: 'textfield',
            name: 'transactionId',
            fieldLabel: '{s name="field/transactionId"}Transaction ID{/s}'
        }, {
            xtype: 'textfield',
            name: 'currency',
            fieldLabel: '{s name="field/currency"}Currency{/s}'
        }, {
            xtype: 'textfield',
            name: 'invoiceAmount',
            itemId: 'invoiceAmount',
            fieldLabel: '{s name="field/invoiceAmount"}Invoice amount{/s}'
        }, {
            xtype: 'base-element-datetime',
            name: 'orderTime',
            fieldLabel: '{s name="field/orderTime"}Order time{/s}',
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
            fieldLabel: '{s name="field/orderStatus"}Order status{/s}'
        }, {
            xtype: 'textfield',
            name: 'paymentStatus',
            itemId: 'paymentStatus',
            fieldLabel: '{s name="field/paymentStatus"}Payment status{/s}'
        }];
    }
});
// {/block}
