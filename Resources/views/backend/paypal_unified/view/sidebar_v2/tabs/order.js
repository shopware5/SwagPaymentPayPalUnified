// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/tabs/order"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.Order', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.tabs.AbstractTab',
    alias: 'widget.paypal-unified-sidebarV2-order',

    title: '{s name="tabs/title/order"}Order{/s}',

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
    },

    /**
     * @param { Object } paypalOrderData
     */
    setOrderData: function(paypalOrderData) {
        this.paypalTransactioinTab.setOrderData(paypalOrderData);
    },
});
// {/block}
