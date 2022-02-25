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

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        this.salutationField = Ext.create('Ext.form.field.Text', {
            name: 'salutation',
            itemId: 'salutation',
            fieldLabel: '{s name="field/salutation"}Salutation{/s}',
            readOnly: true
        });

        this.firstnameField = Ext.create('Ext.form.field.Text', {
            name: 'firstname',
            itemId: 'firstname',
            fieldLabel: '{s name="field/firstName"}First name{/s}',
            readOnly: true
        });

        this.lastnameField = Ext.create('Ext.form.field.Text', {
            name: 'lastname',
            itemId: 'lastname',
            fieldLabel: '{s name="field/lastname"}Last name{/s}',
            readOnly: true
        });

        this.emailField = Ext.create('Ext.form.field.Text', {
            name: 'email',
            itemId: 'email',
            fieldLabel: '{s name="field/email"}E-mail{/s}',
            readOnly: true
        });

        this.groupKeyField = Ext.create('Ext.form.field.Text', {
            name: 'groupKey',
            itemId: 'groupKey',
            fieldLabel: '{s name="field/groupKey"}Group key{/s}',
            readOnly: true
        });

        return [
            this.salutationField,
            this.firstnameField,
            this.lastnameField,
            this.emailField,
            this.groupKeyField,
        ];
    }
});
// {/block}
