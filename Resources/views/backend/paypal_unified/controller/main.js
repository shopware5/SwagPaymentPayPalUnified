//{block name="backend/paypal_unified/controller/main"}
Ext.define('Shopware.apps.PaypalUnified.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Shopware.apps.PaypalUnified.view.overview.Window }
     */
    mainWindow: null,

    init: function () {
        var me = this;

        me.createMainWindow();

        me.callParent(arguments);
    },

    createMainWindow: function () {
        var me = this;

        me.mainWindow = me.getView('overview.Window').create().show();
    }
});
//{/block}