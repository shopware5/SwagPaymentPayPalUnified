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
        'History',
        'ApiV2',
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
        'overview.extensions.Filter',
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
        'sidebar.history.RefundButton',
        'overview.AbstractSidebar',
        'sidebarV2.order.fieldset.AbstractFieldset',
        'sidebarV2.windows.AbstractWindow',
        'sidebarV2.tabs.AbstractTab',
        'sidebarV2.PaymentTypes.PaymentTypes',
        'sidebar.history.RefundButton',
        'overview.SidebarV2',
        'sidebarV2.captureRefund.Toolbar',
        'sidebarV2.fields.FieldFactory',
        'sidebarV2.fields.DateTimeFieldFormatter',
        'sidebarV2.order.fieldset.paymentHistory.RefundButton',
        'sidebarV2.order.fieldset.paymentHistory.PaymentDetails',
        'sidebarV2.order.fieldset.paypalTransactions.InvoiceAmount',
        'sidebarV2.order.fieldset.paypalTransactions.PayerDetails',
        'sidebarV2.order.fieldset.paypalTransactions.PaymentDetails',
        'sidebarV2.order.fieldset.paypalTransactions.ShippingAddress',
        'sidebarV2.order.grid.PaymentHistoryGrid',
        'sidebarV2.order.grid.ProductItemGrid',
        'sidebarV2.tabs.Order',
        'sidebarV2.tabs.PaymentHistory',
        'sidebarV2.tabs.PaypalTransactions',
        'sidebarV2.windows.CaptureWindow',
        'sidebarV2.windows.RefundAuthorizeWindow',
        'sidebarV2.windows.RefundCaptureWindow'
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
