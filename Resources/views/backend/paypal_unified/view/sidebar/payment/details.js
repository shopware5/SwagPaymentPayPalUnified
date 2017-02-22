//{namespace name="backend/paypal_unified/sidebar/payment/details"}
//{block name="backend/paypal_unified/sidebar/payment/details"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.payment.Details', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.paypal-unified-sidebar-payment-details',
    title: '{s name=title}Payment details{/s}',

    anchor: '100%',
    bodyPadding: 5,
    margin: 5,

    defaults: {
        anchor: '100%',
        labelWidth: '130px',
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
            name: 'intent',
            fieldLabel: '{s name=field/intent}Intent{/s}'
        }, {
            xtype: 'textfield',
            name: 'id',
            fieldLabel: '{s name=field/paymentId}Payment ID{/s}'
        }, {
            xtype: 'textfield',
            name: 'cart',
            fieldLabel: '{s name=field/cartId}Cart ID{/s}'
        }, {
            xtype: 'textfield',
            name: 'state',
            fieldLabel: '{s name=field/state}State{/s}'
        }, {
            xtype: 'textfield',
            name: 'create_time',
            itemId: 'createTime',
            fieldLabel: '{s name=field/createTime}Create time{/s}'
        }, {
            xtype: 'textfield',
            itemId: 'updateTime',
            fieldLabel: '{s name=field/updateTime}Update time{/s}'
        }]
    }
});
//{/block}