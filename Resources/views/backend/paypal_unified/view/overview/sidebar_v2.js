// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/overview/sidebarV2"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.SidebarV2', {
    extend: 'Shopware.apps.PaypalUnified.view.overview.AbstractSidebar',
    alias: 'widget.paypal-unified-overview-sidebarV2',

    initComponent: function () {
        this.items = this.createTabs();

        this.callParent(arguments);
    },

    createTabs: function () {
        this.shopwareOrderTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.Order');
        this.paypalTransactioinTab = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.PaypalTransactions');
        this.paymentHistory = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.PaymentHistory');

        return [
            this.shopwareOrderTab,
            this.paypalTransactioinTab,
            this.paymentHistory,
        ];
    },

    setOrderData: function (paypalOrderData) {
        this.paypalTransactioinTab.setOrderData(paypalOrderData);
        this.paymentHistory.setOrderData(paypalOrderData);
    },
});
// {/block}
