//{block name="backend/paypal_unified/controller/main"}
Ext.define('Shopware.apps.PaypalUnified.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Array }
     */
    refs: [
        { ref: 'sidebar', selector: 'paypal-unified-overview-sidebar' }
    ],

    /**
     * @type { Shopware.apps.PaypalUnified.view.overview.Window }
     */
    window: null,

    init: function () {
        var me = this;

        me.createWindow();
        me.createComponentControl();

        me.callParent(arguments);
    },

    createComponentControl: function () {
        var me = this;
        me.control({
            'paypal-unified-overview-grid': {
                'select': me.onSelectGridRecord
            }
        });
    },

    createWindow: function () {
        var me = this;

        me.window = me.getView('overview.Window').create().show();
    },

    /**
     * @param { Ext.selection.RowModel } element
     * @param { Ext.data.Model } record
     */
    onSelectGridRecord: function (element, record) {
        var me = this;
        me.loadDetails(record);
    },

    /**
     * @param { Ext.data.Model } record
     */
    loadDetails: function (record) {
        var me = this,
            sidebar = me.getSidebar();

        sidebar.setLoading('{s name="sidebar/loading/details"}Requesting details from PayPal...{/s}');

        me.updateOrderDetails(record);
        me.updateCustomerDetails(record);

        sidebar.setLoading(false);
        sidebar.enable();
    },

    /**
     * @param { Ext.data.Model } record
     */
    updateOrderDetails: function (record) {
        var me = this,
            sidebar = me.getSidebar();

        sidebar.orderTab.detailsContainer.loadRecord(record);

        //Manually update the following fields.
        sidebar.down('#orderStatus').setValue(record.getOrderStatus().first().get('description'));
        sidebar.down('#paymentStatus').setValue(record.getPaymentStatus().first().get('description'));
        sidebar.down('#orderStatus').setValue(Ext.util.Format.currency(record.get('invoiceAmount')));
    },

    /**
     * @param { Ext.data.Model } record
     */
    updateCustomerDetails: function (record) {
        var me = this,
            customer = record.getCustomer().first().raw, //we use the "raw" property, since the base customer model does not include firstname or lastname.
            customerContainer = me.getSidebar().orderTab.customerContainer;

        customerContainer.down('#salutation').setValue(customer.salutation);
        customerContainer.down('#firstname').setValue(customer.firstname);
        customerContainer.down('#lastname').setValue(customer.lastname);
        customerContainer.down('#email').setValue(customer.email);
        customerContainer.down('#groupKey').setValue(customer.groupKey);
    }
});
//{/block}