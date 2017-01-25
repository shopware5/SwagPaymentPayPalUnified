//{namespace name="backend/paypal_unified/overview/sidebar"}
//{block name="backend/paypal_unified/overview/sidebar"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.Sidebar', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.paypal-unified-overview-sidebar',

    region: 'east',
    layout: 'anchor',
    disabled: true,
    flex: 0.4,
    height: '100%',

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.Order }
     */
    orderTab: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.Payment }
     */
    paymentTab: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.Refund }
     */
    refundTab: null,

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

        me.orderTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.Order');
        me.paymentTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.Payment');
        me.refundTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.Refund');

        items.push(me.orderTab);
        items.push(me.paymentTab);
        items.push(me.refundTab);

        return items;
    }
});
//{/block}