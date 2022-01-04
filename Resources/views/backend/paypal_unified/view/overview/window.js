// {namespace name="backend/paypal_unified/overview/window"}
// {block name="backend/paypal_unified/overview/window"}
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
     * @type { Shopware.apps.PaypalUnified.view.overview.SidebarV2 }
     */
    sidebarV2: null,

    sidebarNames: {
        SIDEBAR_V1: 'SIDEBAR_V1',
        SIDEBAR_V2: 'SIDEBAR_V2',
    },

    /**
     *
     * @returns { Object }
     */
    configure: function() {
        var me = this;
        me.title = '{s name="window/title"}{/s}';

        return {
            listingGrid: 'Shopware.apps.PaypalUnified.view.overview.Grid',
            listingStore: 'Shopware.apps.PaypalUnified.store.Order',
            extensions: [
                {
                    xtype: 'paypal-unified-overview-filter-panel'
                }
            ]
        };
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this,
            items = me.callParent(arguments);

        me.sidebar = Ext.create('Shopware.apps.PaypalUnified.view.overview.Sidebar');
        me.currentSideBar =  me.sidebar

        me.sidebarV2 = Ext.create('Shopware.apps.PaypalUnified.view.overview.SidebarV2');
        me.sidebarV2.hide();

        items.push(me.sidebar);
        items.push(me.sidebarV2);

        return items;
    },

    changeSidebar: function(sidebarName) {
        var me = this;

        me.currentSideBar.setDisabled(true);
        me.currentSideBar.hide();

        switch (sidebarName) {
            case me.sidebarNames.SIDEBAR_V1:
                me.currentSideBar = me.sidebar;
                break;
            case me.sidebarNames.SIDEBAR_V2:
                me.currentSideBar = me.sidebarV2;
                break;
        }

        me.currentSideBar.setDisabled(false);
        me.currentSideBar.show();
    },

    /**
     * @returns { Shopware.apps.PaypalUnified.view.overview.AbstractSidebar }
     */
    getCurrentSidebar: function () {
        return this.currentSideBar;
    },
});
// {/block}
