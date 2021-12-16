// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/overview/abstractSidebar"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.AbstractSidebar', {
    extend: 'Ext.tab.Panel',
    alias: 'widget.paypal-unified-overview-abstractSidebar',

    region: 'east',

    layout: 'anchor',

    disabled: true,

    flex: 0.4,

    height: '100%',

    shopwareOrderTab: null,

    getShopwareOrderTab: function() {
        if (this.shopwareOrderTab === null) {
            throw new Error('Sidebar expects "shopwareOrderTab" to be set');
        }

        return this.shopwareOrderTab;
    },
});
// {/block}
