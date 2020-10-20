// {namespace name="backend/paypal_unified/sidebar/history/details"}
// {block name="backend/paypal_unified/sidebar/history/details"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.history.Details', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.paypal-unified-sidebar-history-details',
    title: '{s name="title"}Details{/s}',

    anchor: '100%',
    bodyPadding: 5,
    margin: 5,
    disabled: true,

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
            name: 'id',
            fieldLabel: '{s name="field/id"}Tracking ID{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'invoice_number',
            fieldLabel: '{s name="field/bookingNumber"}Booking number{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'state',
            fieldLabel: '{s name="field/state"}State{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'total',
            itemId: 'totalAmount',
            fieldLabel: '{s name="field/amount"}Amount{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'value',
            itemId: 'transactionFee',
            fieldLabel: '{s name="field/transactionFee"}Transaction fee{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'payment_mode',
            itemId: 'paymentMode',
            fieldLabel: '{s name="field/paymentMode"}Mode{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'create_time',
            itemId: 'createTime',
            fieldLabel: '{s name="field/createTime"}Create time{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'update_time',
            itemId: 'updateTime',
            fieldLabel: '{s name="field/updateTime"}Update time{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }, {
            xtype: 'textfield',
            name: 'valid_until',
            itemId: 'validUntil',
            fieldLabel: '{s name="field/validUntil"}Valid until{/s}',
            emptyText: '{s name="field/empty"}Not available{/s}'
        }];
    }
});
// {/block}
