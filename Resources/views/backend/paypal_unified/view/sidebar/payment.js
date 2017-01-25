//{namespace name="backend/paypal_unified/sidebar/payment"}
//{block name="backend/paypal_unified/sidebar/payment"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.Payment', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-payment',
    title: '{s name="title"}Payment{/s}',
    autoScroll: true,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.payment.Details }
     */
    detailsContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.payment.Customer }
     */
    customerContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.payment.Address }
     */
    addressContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.payment.Cart }
     */
    cartGrid: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.payment.Invoice }
     */
    invoiceContainer: null,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = [];

        me.detailsContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.payment.Details');
        me.customerContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.payment.Customer');
        me.addressContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.payment.Address');
        me.cartGrid = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.payment.Cart');
        me.invoiceContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.payment.Invoice');

        items.push(me.cartGrid);
        items.push(me.invoiceContainer);
        items.push(me.detailsContainer);
        items.push(me.customerContainer);
        items.push(me.addressContainer);

        return items;
    }
});
//{/block}