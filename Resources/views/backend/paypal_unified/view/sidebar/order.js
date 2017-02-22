//{namespace name="backend/paypal_unified/sidebar/order"}
//{block name="backend/paypal_unified/sidebar/order"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.Order', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-order',
    title: '{s name="title"}Order{/s}',
    autoScroll: true,
    bodyPadding: 5,

    style: {
        background: '#EBEDEF'
    },

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.order.Details }
     */
    detailsContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.order.Customer }
     */
    customerContainer: null,

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

        me.detailsContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.order.Details');
        me.customerContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.order.Customer');

        items.push(me.detailsContainer);
        items.push(me.customerContainer);

        return items;
    }
});
//{/block}