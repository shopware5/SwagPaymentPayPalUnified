//{namespace name="backend/paypal_unified/main"}
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

    /**
     * @type { Object }
     */
    details: null,

    /**
     * @type { String }
     */
    paymentDetailsUrl: '{url module=backend controller=PaypalUnified action=paymentDetails}',

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
            paymentId = record.get('temporaryId'), //The plugin stores the PayPal-PaymentId as temporaryId.
            sidebar = me.getSidebar();

        sidebar.setLoading('{s name="sidebar/loading/details"}Requesting details from PayPal...{/s}');

        me.updateOrderDetails(record);
        me.updateCustomerDetails(record);

        me.requestPaymentDetails(paymentId);
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
        sidebar.down('#invoiceAmount').setValue(Ext.util.Format.currency(record.get('invoiceAmount')));
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
    },

    /**
     * @param { String } paymentId
     */
    requestPaymentDetails: function (paymentId) {
        var me = this;

        Ext.Ajax.request({
            url: me.paymentDetailsUrl,
            params: {
                paymentId: paymentId
            },
            callback: Ext.bind(me.paymentDetailsAjaxCallback, me)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    paymentDetailsAjaxCallback: function (options, success, response) {
        var me = this,
            sidebar = me.getSidebar();

        if (success) {
            me.details = Ext.JSON.decode(response.responseText);

            //Populate the sidebar tab "Payment" with the received data.
            me.updatePaymentDetails();
            me.updatePaymentCustomer();
            me.updatePaymentShipping();
            me.updatePaymentCart();
            me.updatePaymentInvoice();

        } else {
            Shopware.Notification.createGrowlMessage('{s name="sidebar/loading/error"}An error occurred while requesting the PayPal payment details{/s}');
        }

        sidebar.setLoading(false);
        sidebar.enable();
    },

    updatePaymentDetails: function () {
        var me = this,
            detailsContainer = me.getSidebar().paymentTab.detailsContainer,
            detailsModel = Ext.create('Shopware.apps.PaypalUnified.model.Payment', me.details.payment);

        detailsContainer.loadRecord(detailsModel);
        detailsContainer.down('#createTime').setValue(Ext.util.Format.date(detailsModel.get('create_time'), 'd.m.Y H:i:s'));
        detailsContainer.down('#updateTime').setValue(Ext.util.Format.date(detailsModel.get('update_time'), 'd.m.Y H:i:s'));
    },

    updatePaymentCustomer: function () {
        var me = this,
            customerContainer = me.getSidebar().paymentTab.customerContainer,
            customerModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentCustomer', me.details.payment.payer.payer_info);

        customerContainer.loadRecord(customerModel);
    },

    updatePaymentShipping: function () {
        var me = this,
            addressContainer = me.getSidebar().paymentTab.addressContainer,
            shippingModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentCustomerShipping', me.details.payment.payer.payer_info.shipping_address);

        addressContainer.loadRecord(shippingModel);
    },

    updatePaymentCart: function () {
        var me = this,
            cartGrid = me.getSidebar().paymentTab.cartGrid,
            itemList = me.details.payment.transactions[0].item_list.items;

        cartGrid.reconfigure(me.createPaymentCartStore(itemList));
    },

    updatePaymentInvoice: function () {
        var me = this,
            invoiceContainer = me.getSidebar().paymentTab.invoiceContainer,
            invoiceModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentInvoice', me.details.payment.transactions[0].amount),
            invoiceDetailsModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentInvoiceDetails', me.details.payment.transactions[0].amount.details);

        invoiceContainer.loadRecord(invoiceModel);

        invoiceContainer.down('#total').setValue(Ext.util.Format.currency(invoiceModel.get('total')) + ' ' + invoiceModel.get('currency'));
        invoiceContainer.down('#subtotal').setValue(Ext.util.Format.currency(invoiceDetailsModel.get('subtotal')) + ' ' + invoiceModel.get('currency'));
        invoiceContainer.down('#shipping').setValue(Ext.util.Format.currency(invoiceDetailsModel.get('shipping')) + ' ' + invoiceModel.get('currency'));
    },

    /**
     * A helper method that creates a PaymentCart store out of a plain array.
     *
     * @param { Array } lineItems
     * @returns { Shopware.apps.PaypalUnified.store.PaymentCart }
     */
    createPaymentCartStore: function (lineItems) {
        var cartStore = Ext.create('Shopware.apps.PaypalUnified.store.PaymentCart');

        Ext.iterate(lineItems, function(value) {
            var model = Ext.create('Shopware.apps.PaypalUnified.model.PaymentCartItem', value);
            cartStore.add(model);
        });

        return cartStore;
    }
});
//{/block}