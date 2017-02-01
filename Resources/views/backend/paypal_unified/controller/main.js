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
     * @type { Shopware.apps.PaypalUnified.view.refund.Window }
     */
    refundWindow: null,

    /**
     * @type { Object }
     */
    details: null,

    /**
     * @type { String }
     */
    paymentDetailsUrl: '{url module=backend controller=PaypalUnified action=paymentDetails}',

    /**
     * @type { String }
     */
    saleDetailsUrl: '{url module=backend controller=PaypalUnified action=saleDetails}',

    /**
     * @type { String }
     */
    refundDetailsUrl: '{url module=backend controller=PaypalUnified action=refundDetails}',

    /**
     * @type { String }
     */
    refundSaleUrl: '{url module=backend controller=PaypalUnified action=refundSale}',

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
            },
            'paypal-unified-sidebar-refund-sales': {
                'select': me.onSelectSaleGridRecord
            },
            'paypal-unified-sidebar-refund-refund-button': {
                'click': me.onRefundButtonClick
            },
            'paypal-unified-refund-window': {
                'refundSale': me.onRefundSale
            }
        });
    },

    createWindow: function () {
        var me = this;

        me.window = me.getView('overview.Window').create().show();
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
     * @param { Ext.selection.RowModel } element
     * @param { Ext.data.Model } record
     */
    onSelectGridRecord: function (element, record) {
        var me = this;

        me.loadDetails(record);
    },

    /**
     * @param { Ext.selection.RowModel } element
     * @param { Ext.data.Model } record
     * @param { Number } index
     */
    onSelectSaleGridRecord: function (element, record, index) {
        var me = this,
            saleId = record.get('id'),
            isRefund = index !== 0;

        me.requestSaleDetails(saleId, isRefund);
    },

    onRefundButtonClick: function () {
        var me = this;
        me.refundWindow = Ext.create('Shopware.apps.PaypalUnified.view.refund.Window');
        me.refundWindow.show();

        me.updateRefundWindow();
    },

    /**
     * @param { Object } data
     */
    onRefundSale: function (data) {
        var me = this,
            saleId = data.saleId,
            amount = data.amount,
            invoiceNumber = data.invoiceNumber;

        me.requestSaleRefund(saleId, amount, invoiceNumber);
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
     * @param { String } saleId
     * @param { Boolean } isRefund
     */
    requestSaleDetails: function (saleId, isRefund) {
        var me = this,
            ajaxParams = isRefund ? { refundId: saleId } : { saleId: saleId },
            ajaxUrl = isRefund ? me.refundDetailsUrl : me.saleDetailsUrl,
            detailsContainer = me.getSidebar().refundTab.detailsContainer;

        detailsContainer.setLoading('{s name="sidebar/loading/details"}Requesting details from PayPal...{/s}');

        Ext.Ajax.request({
            url: ajaxUrl,
            params: ajaxParams,
            callback: Ext.bind(me.refundDetailsAjaxCallback, me)
        });
    },

    /**
     * @param { String } saleId
     * @param { Numeric } amount
     * @param { String } invoiceNumber
     */
    requestSaleRefund: function (saleId, amount, invoiceNumber) {
        var me = this;

        me.getSidebar().setLoading('{s name="sidebar/loading/sale"}Refunding sale...{/s}');

        Ext.Ajax.request({
            url: me.refundSaleUrl,
            params: {
                saleId: saleId,
                amount: amount,
                invoiceNumber: invoiceNumber
            },
            callback: Ext.bind(me.saleRefundAjaxCallback, me)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    paymentDetailsAjaxCallback: function (options, success, response) {
        var me = this,
            sidebar = me.getSidebar(),
            saleDetailsContainer = sidebar.refundTab.detailsContainer;

        if (success) {
            me.details = Ext.JSON.decode(response.responseText);

            //Populate the sidebar tab "Payment" with the received data.
            me.updatePaymentDetails();
            me.updatePaymentCustomer();
            me.updatePaymentShipping();
            me.updatePaymentCart();
            me.updatePaymentInvoice();
            me.updateRefundSales();

            saleDetailsContainer.disable();
            saleDetailsContainer.loadRecord(null);
        } else {
            Shopware.Notification.createGrowlMessage('{s name="sidebar/loading/error"}An error occurred while requesting the PayPal payment details{/s}');
        }

        sidebar.setLoading(false);
        sidebar.enable();
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    refundDetailsAjaxCallback: function (options, success, response) {
        var me = this,
            details,
            isRefund,
            detailsContainer = me.getSidebar().refundTab.detailsContainer;

        if (success === true) {
             details = Ext.JSON.decode(response.responseText);
             isRefund = Ext.isDefined(details.refund);

            //Populate the sidebar tab "Refund" with the received data.
            me.updateRefundDetails(details, isRefund);

            detailsContainer.enable()
        } else {
            Shopware.Notification.createGrowlMessage('{s name="sidebar/loading/error"}An error occurred while requesting the PayPal payment details{/s}');
            detailsContainer.disable();
        }

        detailsContainer.setLoading(false);
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    saleRefundAjaxCallback: function (options, success, response) {
        var me = this,
            details;

        if (success) {
            details = Ext.JSON.decode(response.responseText);
            me.requestPaymentDetails(me.details.payment.id);
        } else {
            Shopware.Notification.createGrowlMessage('{s name="sidebar/loading/errorRefund"}An error occurred while requesting the PayPal payment details{/s}')
        }

        me.getSidebar().setLoading(false);
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
            amountModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', me.details.payment.transactions[0].amount),
            amountDetailsModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmountDetails', me.details.payment.transactions[0].amount.details),
            currency = amountModel.get('currency');

        invoiceContainer.loadRecord(amountModel);

        invoiceContainer.down('#total').setValue(Ext.util.Format.currency(amountModel.get('total')) + ' ' + currency);
        invoiceContainer.down('#subtotal').setValue(Ext.util.Format.currency(amountDetailsModel.get('subtotal')) + ' ' + currency);
        invoiceContainer.down('#shipping').setValue(Ext.util.Format.currency(amountDetailsModel.get('shipping')) + ' ' + currency);
    },

    updateRefundSales: function ()
    {
       var me = this,
           saleGrid = me.getSidebar().refundTab.salesGrid,
           sales = me.details.sales;

        saleGrid.reconfigure(me.createPaymentSaleStore(sales));
        me.getSidebar().refundTab.refundButton.setDisabled(me.details.sales.maxRefundableAmount === 0);
    },

    /**
     * @param { Object } sale
     * @param { Boolean } isRefund
     */
    updateRefundDetails: function (sale, isRefund)
    {
        /*
           Depending on the isRefund flag,
           the object has different keys for child objects (even though the data is
           almost exactly the same.)
           There are two different types: "sale" and "refund".
           Both are Sale structures but the sale has a transaction_fee object inside which
           we handle separately here.
         */
        var me = this,
            detailsContainer = me.getSidebar().refundTab.detailsContainer,
            detailsModel = isRefund
                ? Ext.create('Shopware.apps.PaypalUnified.model.Sale', sale.refund) //If the object is of type "refund"
                : Ext.create('Shopware.apps.PaypalUnified.model.Sale', sale.sale), //If the object is of type "sale"
            detailsAmountModel = isRefund
                ? Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', sale.refund.amount)
                : Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', sale.sale.amount),
            detailsTransactionFeeModel = isRefund
                ? null
                : Ext.create('Shopware.apps.PaypalUnified.model.SaleTransactionFee', sale.sale.transaction_fee);

        detailsContainer.loadRecord(detailsModel);
        detailsContainer.loadRecord(detailsAmountModel);


        //Only the object of type "sale" has this object.
        //Since we handle both types in this function, this check is required to avoid exceptions.
        if (detailsTransactionFeeModel !== null) {
            detailsContainer.loadRecord(detailsTransactionFeeModel);
            detailsContainer.down('#transactionFee').setValue(Ext.util.Format.currency(detailsTransactionFeeModel.get('value')) + ' ' + detailsTransactionFeeModel.get('currency'));
        }

        detailsContainer.down('#transactionFee').setVisible(!isRefund);
        detailsContainer.down('#paymentMode').setVisible(!isRefund);

        detailsContainer.down('#totalAmount').setValue(Ext.util.Format.currency(detailsAmountModel.get('total')) + ' ' + detailsAmountModel.get('currency'));
        detailsContainer.down('#createTime').setValue(Ext.util.Format.date(detailsModel.get('create_time'), 'd.m.Y H:i:s'));
        detailsContainer.down('#updateTime').setValue(Ext.util.Format.date(detailsModel.get('update_time'), 'd.m.Y H:i:s'));
    },

    updateRefundWindow: function ()
    {
        var me = this,
            refundPanel = me.refundWindow.contentContainer,
            sales = me.details.sales,
            maxRefundableAmount = me.details.sales.maxRefundableAmount,
            initialSale = sales[0],
            saleModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentSale', initialSale);

        //The payment has been partially or completely (does not matter here) refunded already,
        //therefore it does not allow another complete refund.
        refundPanel.down('#refundCompletely').setDisabled(Ext.isDefined(sales[1]));

        refundPanel.loadRecord(saleModel);
        refundPanel.down('#maxAmount').setValue(maxRefundableAmount);
        refundPanel.down('#currentAmount').setMaxValue(maxRefundableAmount);


        ////Reset the value of the amount field.
        refundPanel.down('#currentAmount').setValue();
        refundPanel.down('#invoiceNumber').setValue();
        refundPanel.down('#refundCompletely').setValue(false);
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
    },

    /**
     * A helper method that creates a PaymentSale store out of a plain array.
     *
     * @param { Array } sales
     * @returns { Shopware.apps.PaypalUnified.store.PaymentSale }
     */
    createPaymentSaleStore: function (sales) {
        var saleStore = Ext.create('Shopware.apps.PaypalUnified.store.PaymentSale');

        Ext.iterate(sales, function(key, value) {
            if (key !== 'maxRefundableAmount') {
                var model = Ext.create('Shopware.apps.PaypalUnified.model.PaymentSale', value);
                saleStore.add(model);
            }
        });

        return saleStore;
    }
});
//{/block}