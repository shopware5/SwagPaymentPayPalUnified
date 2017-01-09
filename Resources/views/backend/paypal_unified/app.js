//{block name="backend/paypal_unified/app"}
Ext.define('Shopware.apps.PaypalUnified', {
    extend: 'Enlight.app.SubApplication',
    name: 'Shopware.apps.PaypalUnified',

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
        'UnifiedOrder'
    ],

    /**
     * @type { Array }
     */
    stores: [
        'UnifiedOrder'
    ],

    /**
     * @type { Array }
     */
    views: [
        'overview.Window',
        'overview.Grid',
        'overview.Sidebar',
        'sidebar.Order',
        'sidebar.Payment',
        'sidebar.order.Details',
        'sidebar.order.Customer',
        'sidebar.payment.Details',
        'sidebar.payment.Customer',
        'sidebar.payment.Address'
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