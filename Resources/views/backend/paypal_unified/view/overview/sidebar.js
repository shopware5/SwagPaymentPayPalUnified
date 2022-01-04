// {namespace name="backend/paypal_unified/overview/sidebar"}
// {block name="backend/paypal_unified/overview/sidebar"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.Sidebar', {
    extend: 'Shopware.apps.PaypalUnified.view.overview.AbstractSidebar',
    alias: 'widget.paypal-unified-overview-sidebar',

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.Order }
     */
    orderTab: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.Payment }
     */
    paymentTab: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.History }
     */
    historyTab: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.Toolbar }
     */
    toolbar: null,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = me.createToolbar();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this,
            items = [];

        me.orderTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.Order');
        me.shopwareOrderTab = me.orderTab;

        me.paymentTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.Payment');
        me.historyTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.History');

        items.push(me.orderTab);
        items.push(me.paymentTab);
        items.push(me.historyTab);

        return items;
    },

    /**
     * @returns { Shopware.apps.PaypalUnified.view.sidebar.Toolbar }
     */
    createToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.Toolbar');

        return me.toolbar;
    }
});
// {/block}
