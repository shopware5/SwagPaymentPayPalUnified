// {namespace name="backend/paypal_unified/sidebar/payment/invoice"}
// {block name="backend/paypal_unified/sidebar/payment/invoice"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.payment.Invoice', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.paypal-unified-sidebar-payment-invoice',
    title: '{s name=title}Invoice amount{/s}',

    anchor: '100%',
    bodyPadding: 5,
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
            name: 'total',
            itemId: 'total',
            fieldLabel: '{s name=field/total}Total amount{/s}'
        }, {
            xtype: 'textfield',
            name: 'subtotal',
            itemId: 'subtotal',
            fieldLabel: '{s name=field/subtotal}Subtotal{/s}'
        }, {
            xtype: 'textfield',
            name: 'shipping',
            itemId: 'shipping',
            fieldLabel: '{s name=field/shipping}Shipping{/s}'
        }];
    }
});
// {/block}
