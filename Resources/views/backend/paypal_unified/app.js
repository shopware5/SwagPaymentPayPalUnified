//{block name="backend/paypal_unified/app"}
Ext.define('Shopware.apps.PaypalUnified', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.PaypalUnified',

    /**
     * Enable bulk loading
     *
     * @boolean
     */
    bulkLoad: true,

    /**
     * Sets the loading path for the sub-application.
     *
     * @string
     */
    loadPath: '{url action="load"}',

    /**
     * @array
     */
    controllers: [
        'Main'
    ],

    /**
     * @array
     */
    models: [
        'UnifiedOrder'
    ],

    /**
     * @array
     */
    stores: [
        'UnifiedOrder'
    ],

    /**
     * @array
     */
    views: [
        'overview.Window',
        'overview.List',
        'overview.Sidebar'
    ],

    /**
     * @returns { Shopware.apps.PaypalUnified.view.overview.Window }
     */
    launch: function () {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
//{/block}