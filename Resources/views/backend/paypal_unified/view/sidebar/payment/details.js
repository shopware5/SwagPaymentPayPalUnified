//{namespace name="backend/paypal_unified/sidebar/payment/details"}
//{block name="backend/paypal_unified/sidebar/payment/details"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.payment.Details', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-payment-details',
    title: '{s name=title}Payment details{/s}',

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
            name: 'bookingNumber',
            fieldLabel: '{s name=field/bookingNumber}Booking number{/s}',
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
            name: 'cartId',
            fieldLabel: '{s name=field/cartId}Cart ID{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'status',
            itemId: 'status',
            fieldLabel: '{s name=field/status}Invoice amount{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'creationTime',
            fieldLabel: '{s name=field/creationTime}Creation time{/s}',
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'updatedTime',
            itemId: 'updatedTime',
            fieldLabel: '{s name=field/updatedTime}Updated time{/s}',
            readOnly: true,
            anchor: '100%'
        }]
    }
});
//{/block}