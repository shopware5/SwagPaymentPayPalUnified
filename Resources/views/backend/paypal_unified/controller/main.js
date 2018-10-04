// {namespace name="backend/paypal_unified/controller/main"}
// {block name="backend/paypal_unified/controller/main"}
Ext.define('Shopware.apps.PaypalUnified.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Array }
     */
    refs: [
        { ref: 'sidebar', selector: 'paypal-unified-overview-sidebar' },
        { ref: 'grid', selector: 'paypal-unified-overview-grid' }
    ],

    /**
     * @type { Shopware.apps.PaypalUnified.view.overview.Window }
     */
    window: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.refund.CaptureWindow|Shopware.apps.PaypalUnified.view.refund.SaleWindow }
     */
    refundWindow: null,

    /**
     * @type { Object }
     */
    details: null,

    /**
     * @type { Ext.data.Model }
     */
    record: null,

    /**
     * @type { Shopware.apps.PaypalUnified.controller.Api }
     */
    apiController: null,

    init: function() {
        var me = this;

        me.apiController = me.getController('Api');

        me.createWindow();
        me.createComponentControl();
        me.callParent(arguments);
    },

    createComponentControl: function() {
        var me = this;

        me.control({
            'paypal-unified-overview-grid': {
                'select': me.onSelectGridRecord
            },
            'paypal-unified-sidebar-history-refund-button': {
                'click': me.onRefundButtonClick
            },
            'paypal-unified-refund-sale-window': {
                'refundSale': me.onRefundSale
            },
            'paypal-unified-capture-authorize': {
                'authorizePayment': me.onAuthorizePayment
            },
            'paypal-unified-refund-capture-window': {
                'refundCapture': me.onRefundCapture
            },
            'paypal-unified-sidebar-order-actions': {
                'voidAuthorization': me.onVoidAuthorization,
                'voidOrder': me.onVoidOrder
            }
        });
    },

    createWindow: function() {
        var me = this;

        me.window = me.getView('overview.Window').create().show();
    },

    /**
     * @param { Ext.selection.RowModel } element
     * @param { Ext.data.Model } record
     */
    onSelectGridRecord: function(element, record) {
        var me = this;

        me.loadDetails(record);
    },

    onRefundButtonClick: function() {
        var me = this;

        if (me.details.authorization || me.details.order) {
            me.refundWindow = Ext.create('Shopware.apps.PaypalUnified.view.refund.CaptureWindow');
            me.refundWindow.show();
            me.updateCaptureRefundWindow();
        } else {
            me.refundWindow = Ext.create('Shopware.apps.PaypalUnified.view.refund.SaleWindow');
            me.refundWindow.show();
            me.updateSaleRefundWindow();
        }
    },

    onVoidAuthorization: function() {
        var me = this;

        me.getSidebar().setLoading('{s name=sidebar/loading/voiding}Voiding payment...{/s}');
        me.apiController.voidAuthorization(Ext.bind(me.voidCallback, me));
    },

    onVoidOrder: function() {
        var me = this;

        me.getSidebar().setLoading('{s name=sidebar/loading/voiding}Voiding payment...{/s}');
        me.apiController.voidOrder(Ext.bind(me.voidCallback, me));
    },

    /**
     * @param { Object } data
     */
    onRefundSale: function(data) {
        var me = this,
            amount = data.amount,
            invoiceNumber = data.invoiceNumber;

        me.getSidebar().setLoading('{s name=sidebar/loading/refunding}Refunding payment...{/s}');
        me.apiController.refundSale(amount, invoiceNumber, Ext.bind(me.refundCallback, me));
    },

    /**
     * @param { Object } data
     */
    onRefundCapture: function(data) {
        var me = this,
            captureId = data.id,
            amount = data.amount,
            note = data.note;

        me.getSidebar().setLoading('{s name=sidebar/loading/refunding}Refunding payment...{/s}');
        me.apiController.refundCapture(captureId, amount, note, Ext.bind(me.refundCallback, me));
    },

    /**
     * @param { Numeric } amount
     * @param { Boolean } isFinal
     */
    onAuthorizePayment: function(amount, isFinal) {
        var me = this;

        me.getSidebar().setLoading('{s name=sidebar/loading/authorizing}Authorizing payment...{/s}');

        if (me.details.payment.intent === 'authorize') {
            me.apiController.captureAuthorization(amount, isFinal, Ext.bind(me.captureCallback, me));
        } else {
            me.apiController.captureOrder(amount, isFinal, Ext.bind(me.captureCallback, me));
        }
    },

    captureCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', '{s name=growl/authorizeSuccess}The payment has been authorized successfully{/s}', me.window.title);

            me.loadDetails(me.record);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', responseObject.message, me.window.title);
        }

        me.getSidebar().setLoading(false);
    },

    voidCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', '{s name=growl/voidSuccess}The payment has been voided successfully.{/s}', me.window.title);

            me.loadDetails(me.record);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', responseObject.message, me.window.title);
        }

        me.getSidebar().setLoading(false);
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    paymentDetailsCallback: function(options, success, response) {
        var me = this,
            sidebar = me.getSidebar(),
            details = Ext.JSON.decode(response.responseText);

        if (!Ext.isDefined(details) || !details.success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', details.message, me.window.title);

            sidebar.setLoading(false);
            sidebar.disable();
            return;
        }

        me.details = details;

        if (details.legacy) {
            me.displayLegacyDetails();
        } else {
            me.displayUnifiedDetails();
        }

        sidebar.setLoading(false);
        sidebar.enable();
    },

    displayUnifiedDetails: function() {
        var me = this,
            sidebar = me.getSidebar(),
            saleDetailsContainer = sidebar.historyTab;

        // Populate the sidebar tab "Payment" with the received data.
        me.updatePaymentDetails();
        me.updatePaymentCustomer();
        me.updatePaymentShipping();
        me.updatePaymentCart();
        me.updatePaymentInvoice();
        me.updateRefundSales();
        me.updateWindowOptions();

        sidebar.paymentTab.cartGrid.show();
        sidebar.paymentTab.addressContainer.show();
        sidebar.paymentTab.customerContainer.show();

        sidebar.paymentTab.setLegacyWarning(false);
        sidebar.historyTab.setLegacyWarning(false);

        saleDetailsContainer.detailsContainer.disable();
    },

    displayLegacyDetails: function() {
        var me = this,
            sidebar = me.getSidebar();

        // Populate the sidebar tab "Payment" with the received data.
        me.updatePaymentDetails();
        me.updatePaymentInvoice();
        me.updateRefundSales();

        // Hide the information we don't have
        sidebar.paymentTab.cartGrid.hide();
        sidebar.paymentTab.addressContainer.hide();
        sidebar.paymentTab.customerContainer.hide();

        sidebar.paymentTab.setLegacyWarning(true);
        sidebar.historyTab.setLegacyWarning(true);
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    refundCallback: function(options, success, response) {
        var me = this,
            details = Ext.JSON.decode(response.responseText);

        if (Ext.isDefined(details) && details.success) {
            me.loadDetails(me.record);

            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', '{s name=growl/refundSuccess}The refund was successful{/s}', me.window.title);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal{/s}', details.message, me.window.title);
        }

        me.getSidebar().setLoading(false);
        me.getGrid().getStore().reload();
    },

    /**
     * @param { Ext.data.Model } record
     */
    loadDetails: function(record) {
        var me = this,
            paymentId = record.get('temporaryId'), // The plugin stores the PayPal-PaymentId as temporaryId.
            transactionId = record.get('transactionId'), // The plugin stores the PayPal-PaymentId as temporaryId.
            paymentMethodId = record.get('paymentId'),
            sidebar = me.getSidebar();

        sidebar.setLoading('{s name=sidebar/loading/details}Requesting details from PayPal...{/s}');

        me.record = record;
        me.updateOrderDetails(record);
        me.updateCustomerDetails(record);

        me.apiController.getPaymentById(paymentId, paymentMethodId, transactionId, Ext.bind(me.paymentDetailsCallback, me));
    },

    /**
     * @param { Ext.data.Model } record
     */
    updateOrderDetails: function(record) {
        var me = this,
            sidebar = me.getSidebar();

        sidebar.orderTab.loadRecord(record);

        // Manually update the following fields.
        sidebar.down('#orderStatus').setValue(record.getOrderStatus().first().get('description'));
        sidebar.down('#paymentStatus').setValue(record.getPaymentStatus().first().get('description'));
        sidebar.down('#invoiceAmount').setValue(Ext.util.Format.currency(record.get('invoiceAmount')));
    },

    /**
     * @param { Ext.data.Model } record
     */
    updateCustomerDetails: function(record) {
        var me = this,
            customer = record.getCustomer().first().raw, // we use the "raw" property, since the base customer model does not include firstname or lastname.
            customerContainer = me.getSidebar().orderTab.customerContainer;

        customerContainer.down('#salutation').setValue(customer.salutation);
        customerContainer.down('#firstname').setValue(customer.firstname);
        customerContainer.down('#lastname').setValue(customer.lastname);
        customerContainer.down('#email').setValue(customer.email);
        customerContainer.down('#groupKey').setValue(customer.groupKey);
    },

    updatePaymentDetails: function() {
        var me = this,
            detailsContainer = me.getSidebar().paymentTab,
            detailsModel = Ext.create('Shopware.apps.PaypalUnified.model.Payment', me.details.payment);

        detailsContainer.loadRecord(detailsModel);
        detailsContainer.down('#createTime').setValue(Ext.util.Format.date(detailsModel.get('create_time'), 'd.m.Y H:i:s'));
        detailsContainer.down('#updateTime').setValue(Ext.util.Format.date(detailsModel.get('update_time'), 'd.m.Y H:i:s'));
    },

    updatePaymentCustomer: function() {
        var me = this,
            customerContainer = me.getSidebar().paymentTab,
            customerModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentCustomer', me.details.payment.payer.payer_info);

        customerContainer.loadRecord(customerModel);
    },

    updatePaymentShipping: function() {
        var me = this,
            addressContainer = me.getSidebar().paymentTab,
            shippingModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentCustomerShipping', me.details.payment.payer.payer_info.shipping_address);

        addressContainer.loadRecord(shippingModel);
    },

    updatePaymentCart: function() {
        var me = this,
            cartGrid = me.getSidebar().paymentTab.cartGrid,
            itemList = me.details.payment.transactions[0].item_list.items;

        cartGrid.reconfigure(me.createPaymentCartStore(itemList));
    },

    updatePaymentInvoice: function() {
        var me = this,
            invoiceContainer = me.getSidebar().paymentTab,
            amountModel = me.details.legacy ? Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', me.details.payment.amount)
                : Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', me.details.payment.transactions[0].amount),
            amountDetailsModel = me.details.legacy ? Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmountDetails', me.details.payment.amount.details)
                : Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmountDetails', me.details.payment.transactions[0].amount.details),
            currency = amountModel.get('currency');

        invoiceContainer.loadRecord(amountModel);

        invoiceContainer.down('#total').setValue(Ext.util.Format.currency(amountModel.get('total')) + ' ' + currency);
        invoiceContainer.down('#subtotal').setValue(Ext.util.Format.currency(amountDetailsModel.get('subtotal')) + ' ' + currency);
        invoiceContainer.down('#shipping').setValue(Ext.util.Format.currency(amountDetailsModel.get('shipping')) + ' ' + currency);
    },

    updateRefundSales: function() {
        var me = this,
            saleGrid = me.getSidebar().historyTab.salesGrid,
            history = me.details.history;

        saleGrid.reconfigure(me.createPaymentHistoryStore(history));
        me.getSidebar().historyTab.refundButton.setDisabled(me.details.history.maxRefundableAmount === 0);
    },

    updateSaleRefundWindow: function() {
        var me = this,
            refundPanel = me.refundWindow.contentContainer,
            history = me.details.history,
            maxRefundableAmount = me.details.history.maxRefundableAmount,
            initialSale = me.details.legacy ? me.details.payment : me.details.sale,
            saleModel = Ext.create('Shopware.apps.PaypalUnified.model.PaymentSale', initialSale);

        me.refundWindow.currency = initialSale.amount.currency;

        refundPanel.loadRecord(saleModel);
        refundPanel.down('#maxAmount').setValue(maxRefundableAmount);
        refundPanel.down('#currentAmount').setMaxValue(maxRefundableAmount);

        // Reset the value of the amount field.
        refundPanel.down('#currentAmount').setValue();
        refundPanel.down('#invoiceNumber').setValue();
        refundPanel.down('#refundCompletely').setValue(false);

        refundPanel.down('#refundCompletely').setDisabled(Ext.isDefined(history[1]));
    },

    /**
     * A helper function that parses all available captures for the selected payment
     * and hands them to the capture refund window.
     */
    updateCaptureRefundWindow: function() {
        var me = this,
            captures = [],
            currency = '';

        Ext.iterate(me.details.history, function(key, value) {
            if (value.type === 'capture') {
                value.description = Ext.util.Format.date(value.create_time) + ' (' + Ext.util.Format.currency(value.amount) + ' ' + value.currency + ') - ' + value.id;
                captures.push(value);
                currency = value.currency;
            }
        });

        me.refundWindow.currency = currency;
        me.refundWindow.setCaptures(captures);
    },

    /**
     * A helper function that updates the window options.
     */
    updateWindowOptions: function() {
        var me = this,
            isAuthorization = typeof (me.details.authorization) !== 'undefined',
            isSale = typeof (me.details.sale) !== 'undefined',
            isOrder = typeof (me.details.order) !== 'undefined';

        if (isOrder) {
            me.updateWindowByOrder();
        } else if (isAuthorization) {
            me.updateWindowByAuthorization();
        } else if (isSale) {
            me.updateWindowBySale();
        }
    },

    /**
     * A helper method that updates the window corresponding to the payment intent.
     *
     * For sale:
     *      - Enable the refund option
     *      - Disable all toolbar options
     */
    updateWindowBySale: function() {
        var me = this,
            refundButton = me.getSidebar().historyTab.down('#refundButton'),
            toolbar = me.getSidebar().toolbar,
            payment = me.details.payment;

        refundButton.enable();
        toolbar.updateToolbar(payment.intent, me.details.history.maxRefundableAmount, false);
    },

    /**
     * A helper method that updates the window corresponding to the payment intent.
     *
     * For authorization:
     *      state "authorized":
     *          - Disable the refund option
     *          - Enable all toolbar options
     *      state "partially_captured"
     *          - Enable the refund option
     *          - Enable the capture toolbar option
     *          - Disable the void toolbar option
     *      state "captured"
     *          - Enable the refund option
     *          - Disable all toolbar options
     *      state "voided"
     *          - Disable the refund option
     *          - Disable the toolbar
     */
    updateWindowByAuthorization: function() {
        var me = this,
            refundButton = me.getSidebar().historyTab.down('#refundButton'),
            toolbar = me.getSidebar().toolbar,
            payment = me.details.payment,
            authorization = me.details.authorization;

        if (authorization.state === 'authorized') {
            refundButton.disable();
            toolbar.updateToolbar(payment.intent, me.details.history.maxAuthorizableAmount, true);
        } else if (authorization.state === 'partially_captured') {
            refundButton.enable();
            toolbar.updateToolbar(payment.intent, me.details.history.maxAuthorizableAmount, false);
        } else if (authorization.state === 'captured') {
            refundButton.enable();
            toolbar.updateToolbar(payment.intent, 0, false);
        } else if (authorization.state === 'voided') {
            refundButton.disable();
            toolbar.updateToolbar(payment.intent, 0, false);
        }
    },

    /**
     * A helper method that updates the window corresponding to the payment intent.
     *
     * For order:
     *      state "PENDING":
     *          - Disable the refund option
     *          - Enable all toolbar options
     *      state "CAPTURE":
     *          - Enable the refund option
     *          - Enable the capture toolbar option
     *          - Disable the void toolbar option
     *      state "VOIDED":
     *          - Disable the refund option
     *          - Disable all toolbar options
     */
    updateWindowByOrder: function() {
        var me = this,
            refundButton = me.getSidebar().historyTab.down('#refundButton'),
            toolbar = me.getSidebar().toolbar,
            payment = me.details.payment,
            order = me.details.order;

        if (order.state === 'PENDING') {
            refundButton.disable();
            toolbar.updateToolbar(payment.intent, me.details.history.maxAuthorizableAmount, true);
        } else if (order.state === 'CAPTURE') {
            refundButton.enable();
            toolbar.updateToolbar(payment.intent, me.details.history.maxAuthorizableAmount, false);
        } else if (order.state === 'VOIDED') {
            refundButton.disable();
            toolbar.updateToolbar(payment.intent, 0, false);
        }
    },

    /**
     * A helper method that creates a PaymentCart store out of a plain array.
     *
     * @param { Array } lineItems
     * @returns { Shopware.apps.PaypalUnified.store.PaymentCart }
     */
    createPaymentCartStore: function(lineItems) {
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
    createPaymentHistoryStore: function(sales) {
        var saleStore = Ext.create('Shopware.apps.PaypalUnified.store.PaymentSale');

        Ext.iterate(sales, function(key, value) {
            if (key !== 'maxRefundableAmount' && key !== 'maxAuthorizableAmount') {
                var model = Ext.create('Shopware.apps.PaypalUnified.model.PaymentSale', value);
                saleStore.add(model);
            }
        });

        return saleStore;
    },

    /**
     * A helper method used by the mixin.
     *
     * @returns { Ext.data.Model }
     */
    getRecord: function() {
        var me = this;
        return me.record;
    },

    /**
     * A helper method used by the mixin.
     *
     * @returns { Object }
     */
    getDetails: function() {
        var me = this;
        return me.details;
    }
});
// {/block}
