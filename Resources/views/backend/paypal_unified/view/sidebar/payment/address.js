//{namespace name="backend/paypal_unified/sidebar/payment/address"}
//{block name="backend/paypal_unified/sidebar/payment/address"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.payment.Address', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-payment-address',
    title: '{s name=title}Shipping address{/s}',

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
            name: 'recipient_name',
            fieldLabel: '{s name="field/recipient"}Recipient{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'line1',
            fieldLabel: '{s name="field/street"}Street{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'city',
            fieldLabel: '{s name="field/city"}City{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'state',
            fieldLabel: '{s name="field/state"}State{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'postal_code',
            fieldLabel: '{s name="field/postalCode"}Postal code{/s}',
            readOnly: true,
            anchor: '100%'
        }, {
            xtype: 'textfield',
            name: 'country_code',
            fieldLabel: '{s name="field/country"}Country{/s}',
            readOnly: true,
            anchor: '100%'
        }];
    }
});
//{/block}