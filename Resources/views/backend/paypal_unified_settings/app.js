// {block name="backend/paypal_unified_settings/app"}
Ext.define('Shopware.apps.PaypalUnifiedSettings', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.PaypalUnifiedSettings',

    /**
     * Enable bulk loading
     *
     * @type { Boolean }
     */
    bulkLoad: true,

    /**
     * Sets the loading path for the sub-application.
     *
     * @type { String }
     */
    loadPath: '{url action="load"}',

    /**
     * @type { Array }
     */
    controllers: [
        'Main'
    ],

    /**
     * @type { Array }
     */
    models: [
        'General',
        'ExpressCheckout',
        'Installments',
        'Plus'
    ],

    /**
     * @type { Array }
     */
    stores: [
        'LogLevel',
        'EcButtonStyleColor',
        'EcButtonStyleShape',
        'EcButtonStyleSize'
    ],

    /**
     * @type { Array }
     */
    views: [
        'Window',
        'Toolbar',
        'TopToolbar',
        'tabs.General',
        'tabs.Plus',
        'tabs.Installments',
        'tabs.ExpressCheckout'
    ],

    /**
     * @returns { Shopware.apps.PaypalUnifiedSettings.view.Window }
     */
    launch: function() {
        var me = this,
            settingsController = me.getController('Main');

        return settingsController.mainWindow;
    }
});
// {/block}
