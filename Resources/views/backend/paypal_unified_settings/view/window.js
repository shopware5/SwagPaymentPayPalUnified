// {namespace name="backend/paypal_unified_settings/window"}
// {block name="backend/paypal_unified_settings/window"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=title}PayPal - Settings{/s}',
    alias: 'widget.paypal-unified-settings-window',

    height: '70%',
    width: '45%',
    layout: 'anchor',
    autoScroll: true,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.Toolbar }
     */
    toolbar: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.TopToolbar }
     */
    topToolbar: null,

    /**
     * @type { Ext.tab.Panel }
     */
    tabContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.tabs.General }
     */
    generalTab: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.tabs.Plus }
     */
    paypalPlusTab: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.tabs.Installments }
     */
    paypalInstallmentsTab: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.tabs.ExpressCheckout }
     */
    paypalEcTab: null,

    /**
     * @type { Shopware.data.Model }
     */
    record: null,

    initComponent: function() {
        var me = this;

        me.dockedItems = [me.createToolbar(), me.createTopToolbar()];
        me.items = me.createItems();

        me.callParent(arguments);

        // Manually set the background color of the window body.
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
    createToolbar: function() {
        var me = this;

        me.toolbar = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.Toolbar');

        return me.toolbar;
    },

    /**
     * @returns { Ext.tab.Panel }
     */
    createTabElement: function() {
        var me = this;

        me.generalTab = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.tabs.General');
        me.paypalPlusTab = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.tabs.Plus');
        me.paypalInstallmentsTab = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.tabs.Installments');
        me.paypalEcTab = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.tabs.ExpressCheckout');

        me.tabContainer = Ext.create('Ext.tab.Panel', {
            border: false,
            style: {
                background: '#EBEDEF'
            },

            items: [
                me.generalTab,
                me.paypalEcTab,
                me.paypalPlusTab,
                me.paypalInstallmentsTab
            ]
        });

        return me.tabContainer;
    },

    /**
     * @returns { Shopware.apps.PaypalUnifiedSettings.view.TopToolbar }
     */
    createTopToolbar: function() {
        var me = this;

        me.topToolbar = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.TopToolbar');

        return me.topToolbar;
    }
});
// {/block}
