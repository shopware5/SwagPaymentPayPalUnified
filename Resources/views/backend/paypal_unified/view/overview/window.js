//{namespace name="backend/paypal_unified/overview/window"}
//{block name="backend/paypal_unified/overview/window"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.paypal-unified-overview-window',

    height: '80%',
    width: '90%',

    /**
     * @Shopware.apps.PaypalUnified.view.overview.Sidebar
     */
    sidebar: null,

    /**
     *
     * @returns { Object }
     */
    configure: function () {
        var me = this;
        me.title = '{s name="window/title"}{/s}';

        return {
            listingGrid: 'Shopware.apps.PaypalUnified.view.overview.List',
            listingStore: 'Shopware.apps.PaypalUnified.store.UnifiedOrder'
        };
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = me.callParent();

        me.sidebar = Ext.create('Shopware.apps.PaypalUnified.view.overview.Sidebar');
        items.push(me.sidebar);

        return items;
    }
});
//{/block}