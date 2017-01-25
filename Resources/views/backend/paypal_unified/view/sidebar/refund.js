//{namespace name="backend/paypal_unified/sidebar/refund"}
//{block name="backend/paypal_unified/sidebar/refund"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.Refund', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-refund',
    title: '{s name="title"}Refund{/s}',
    autoScroll: true,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.refund.Sales }
     */
    salesGrid: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.refund.Details }
     */
    detailsContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.refund.RefundButton }
     */
    refundButton: null,

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

        me.salesGrid = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.refund.Sales');
        me.refundButton = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.refund.RefundButton');
        me.detailsContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.refund.Details');

        items.push(me.salesGrid);
        items.push(me.detailsContainer);
        items.push(me.refundButton);

        return items;
    }
});
//{/block}