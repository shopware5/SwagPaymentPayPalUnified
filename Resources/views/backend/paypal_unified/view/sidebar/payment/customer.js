//{namespace name="backend/paypal_unified/sidebar/payment/customer"}
//{block name="backend/paypal_unified/sidebar/payment/customer"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.payment.Customer', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-payment-customer',
    title: '{s name=title}Customer{/s}',

    anchor: '100%',
    bodyPadding: 5,
    margin: 5,
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
            name: 'payer_id',
            fieldLabel: '{s name="field/payerId"}Payer ID{/s}'
        }, {
            xtype: 'textfield',
            name: 'email',
            fieldLabel: '{s name="field/email"}E-mail{/s}'
        }, {
            xtype: 'textfield',
            name: 'first_name',
            fieldLabel: '{s name="field/firstName"}First name{/s}'
        }, {
            xtype: 'textfield',
            name: 'last_name',
            fieldLabel: '{s name="field/lastName"}Last name{/s}'
        }, {
            xtype: 'textfield',
            name: 'phone',
            fieldLabel: '{s name="field/phone"}Phone number{/s}'
        }, {
            xtype: 'textfield',
            name: 'country_code',
            fieldLabel: '{s name="field/country"}Country code{/s}'
        }];
    }
});
//{/block}