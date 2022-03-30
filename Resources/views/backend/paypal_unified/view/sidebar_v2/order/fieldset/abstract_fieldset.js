// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/AbstractFieldset"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.AbstractFieldset', {
    extend: 'Ext.form.FieldSet',

    bodyPadding: 5,
    margin: 5,

    initComponent: function() {
        this.fieldFactory = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.fields.FieldFactory');
        this.dateTimeFormatter = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.fields.DateTimeFieldFormatter');

        this.items = this.createItems();

        this.callParent(arguments);
    },

    /**
     * @return { Array }
     */
    createItems: function() {
        throw new Error('The method "createItems" should be overwritten');
    },
});
// {/block}
