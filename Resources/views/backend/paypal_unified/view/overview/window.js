//{namespace name="backend/paypal_unified/overview/window"}
//{block name="backend/paypal_unified/overview/window"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.paypal-unified-overview-window',

    height: '70%',
    width: '80%',

    /**
     * @type { Shopware.apps.PaypalUnified.view.overview.Sidebar }
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
            listingGrid: 'Shopware.apps.PaypalUnified.view.overview.Grid',
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