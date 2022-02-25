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
        this.numberField = Ext.create('Ext.form.field.Text', {
            name: 'number',
            fieldLabel: '{s name="field/number"}Order number{/s}',
            readOnly: true
        });

        this.transactionIdField = Ext.create('Ext.form.field.Text', {
            name: 'transactionId',
            fieldLabel: '{s name="field/transactionId"}Transaction ID{/s}',
            readOnly: true
        });

        this.currencyField = Ext.create('Ext.form.field.Text', {
            name: 'currency',
            fieldLabel: '{s name="field/currency"}Currency{/s}',
            readOnly: true
        });

        this.invoiceAmountField = Ext.create('Ext.form.field.Text', {
            name: 'invoiceAmount',
            itemId: 'invoiceAmount',
            fieldLabel: '{s name="field/invoiceAmount"}Invoice amount{/s}',
            readOnly: true
        });

        this.orderTimeField = Ext.create('Shopware.apps.Base.view.element.DateTime', {
            name: 'orderTime',
            fieldLabel: '{s name="field/orderTime"}Order time{/s}',
            dateCfg: {
                readOnly: true
            },
            timeCfg: {
                readOnly: true
            },
            readOnly: true
        });

        this.orderStatusField = Ext.create('Ext.form.field.Text', {
            name: 'orderStatus',
            itemId: 'orderStatus',
            fieldLabel: '{s name="field/orderStatus"}Order status{/s}',
            readOnly: true
        });

        this.paymentStatusField = Ext.create('Ext.form.field.Text', {
            name: 'paymentStatus',
            itemId: 'paymentStatus',
            fieldLabel: '{s name="field/paymentStatus"}Payment status{/s}',
            readOnly: true
        });

        return [
            this.numberField,
            this.transactionIdField,
            this.currencyField,
            this.invoiceAmountField,
            this.orderTimeField,
            this.orderStatusField,
            this.paymentStatusField,
        ];
    }
});
// {/block}
