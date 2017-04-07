// {block name="backend/paypal_unified/app"}
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
        'Main',
        'Api',
        'History'
    ],

    /**
     * @type { Array }
     */
    models: [
        'ShopwareOrder',
        'Payment',
        'PaymentCustomer',
        'PaymentCartItem',
        'PaymentAmount',
        'PaymentAmountDetails',
        'PaymentCustomerShipping',
        'Sale',
        'Refund',
        'Capture',
        'Order',
        'Authorization',
        'TransactionFee'
    ],

    /**
     * @type { Array }
     */
    stores: [
        'Order',
        'PaymentCart'
    ],

    /**
     * @type { Array }
     */
    views: [
        'refund.SaleWindow',
        'refund.CaptureWindow',
        'overview.Window',
        'overview.Grid',
        'overview.Sidebar',
        'capture.Authorize',
        'sidebar.Order',
        'sidebar.Payment',
        'sidebar.History',
        'sidebar.Toolbar',
        'sidebar.order.Details',
        'sidebar.order.Customer',
        'sidebar.payment.Details',
        'sidebar.payment.Customer',
        'sidebar.payment.Address',
        'sidebar.payment.Invoice',
        'sidebar.payment.Cart',
        'sidebar.history.Grid',
        'sidebar.history.Details',
        'sidebar.history.RefundButton'
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
// {/block}
