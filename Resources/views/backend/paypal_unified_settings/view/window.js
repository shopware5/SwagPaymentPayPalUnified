//{namespace name="backend/paypal_unified_settings/window"}
//{block name="backend/paypal_unified_settings/window"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=title}PayPal Unified - Settings{/s}',
    alias: 'widget.paypal-unified-settings-window',

    maximizable: false,
    resizable: false,
    height: '65%',
    width: '40%',
    layout: 'anchor',

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.Toolbar }
     */
    toolbar: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.ShopSelection }
     */
    shopSelection: null,

    /**
     * @type { Ext.tab.Panel }
     */
    tabContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.tabs.General }
     */
    generalTab: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.tabs.PaypalPlus }
     */
    paypalPlusTab: null,

    /**
     * @type { Shopware.data.Model }
     */
    record: null,

    initComponent: function () {
        var me = this;

        me.dockedItems = [ me.createToolbar(), me.createShopSelection() ];
        me.items = me.createItems();

        me.callParent(arguments);

        //Manually set the background color of the window body.
        me.setBodyStyle({
            background: '#EBEDEF'
        });
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this,
            items = [];

        items.push(me.createTabElement());

        return items;
    },

    /**
     * @returns { Shopware.apps.PaypalUnifiedSettings.view.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.Toolbar');

        return me.toolbar;
    },

    /**
     * @returns { Ext.tab.Panel }
     */
    createTabElement: function () {
        var me = this;

        me.generalTab = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.tabs.General');
        me.paypalPlusTab = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.tabs.PaypalPlus');

        me.tabContainer = Ext.create('Ext.tab.Panel', {
            border: false,
            style: {
                background: '#EBEDEF'
            },

            items: [ me.generalTab, me.paypalPlusTab ]
        });

        return me.tabContainer;
    },

    /**
     * @returns { Shopware.apps.PaypalUnifiedSettings.view.ShopSelection }
     */
    createShopSelection: function () {
        var me = this;

        me.shopSelection = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.ShopSelection');

        return me.shopSelection;
    }
});
//{/block}