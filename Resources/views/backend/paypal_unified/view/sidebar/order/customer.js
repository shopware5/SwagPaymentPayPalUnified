// {namespace name="backend/paypal_unified/sidebar/order/customer"}
// {block name="backend/paypal_unified/sidebar/order/customer"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.order.Customer', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.paypal-unified-sidebar-order-customer',
    title: '{s name="title"}Customer details{/s}',

    defaults: {
        anchor: '100%',
        labelWidth: 130,
        readOnly: true
    },

    style: {
        background: '#EBEDEF'
    },

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
            name: 'salutation',
            itemId: 'salutation',
            fieldLabel: '{s name="field/salutation"}Salutation{/s}'
        }, {
            xtype: 'textfield',
            name: 'firstname',
            itemId: 'firstname',
            fieldLabel: '{s name="field/firstName"}First name{/s}'
        }, {
            xtype: 'textfield',
            name: 'lastname',
            itemId: 'lastname',
            fieldLabel: '{s name="field/lastname"}Last name{/s}'
        }, {
            xtype: 'textfield',
            name: 'email',
            itemId: 'email',
            fieldLabel: '{s name="field/email"}E-mail{/s}'
        }, {
            xtype: 'textfield',
            name: 'groupKey',
            itemId: 'groupKey',
            fieldLabel: '{s name="field/groupKey"}Group key{/s}'
        }];
    }
});
// {/block}
