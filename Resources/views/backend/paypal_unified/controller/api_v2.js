// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/controller/apiV2"}
Ext.define('Shopware.apps.PaypalUnified.controller.ApiV2', {
    extend: 'Enlight.app.Controller',

    paymentTypes: Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.PaymentTypes.PaymentTypes'),

    /**
     * @type { Array }
     */
    apiV2Types: [
        'PayPalClassicV2',
        'PayPalPlusInvoiceV2',
        'PayPalExpressV2',
        'PayPalSmartPaymentButtonsV2'
    ],

    /**
     * @type { Object }
     */
    urls: {
        orderDetails: '{url module=backend controller=PaypalUnifiedV2 action=orderDetails}',
        captureOrder: '{url module=backend controller=PaypalUnifiedV2 action=captureOrder}',
        refundOrder: '{url module=backend controller=PaypalUnifiedV2 action=refundOrder}',
        cancelAuthorization: '{url module=backend controller=PaypalUnifiedV2 action=cancelAuthorization}',
    },

    /**
     * @type { Array }
     */
    refs: [{
        ref: 'window', selector: 'paypal-unified-overview-window'
    }, {
        ref: 'paymentDetailFieldset', selector: 'paypal-unified-sidebar-order-fieldset-payment-history-PaymentDetails'
    }, {
        ref: 'paymentHistoryGrid', selector: 'paypal-unified-order-payment-history-grid-v2'
    }],

    init: function() {
        this.registerElementEvents();

        this.callParent(arguments);
    },

    registerElementEvents: function() {
        this.control({
            'paypal-unified-v2-actions-refund-capture-window': {
                executeAction: this.onExecuteRefund
            },

            'paypal-unified-v2-actions-refund-authorize-window': {
                executeAction: this.onExecuteRefund
            },

            'paypal-unified-v2-actions-capture-window': {
                executeAction: this.onExecuteCapture
            },

            'paypal-unified-overview-grid': {
                select: this.onSelectGridRecord
            },

            'paypal-unified-sidebarV2-PaymentHistory': {
                capture: this.onCaptureButtonClick,
                cancelAuthorization: this.onCancelAuthorization
            },

            'paypal-unified-sidebar-order-fieldset-paymentHistory-refundButton': {
                refundButtonClick: this.onRefundButtonClick,
            },

            'paypal-unified-order-payment-history-grid-v2': {
                onSelectionChange: this.onHistoryGridChange
            },
        });
    },

    /**
     * @param { Ext.selection.RowModel } row
     * @param { Ext.data.Model } record
     */
    onSelectGridRecord: function(row, record) {
        if (!Ext.Array.contains(this.apiV2Types, record.get('paymentType'))) {
            return;
        }

        var paymentId = record.get('temporaryId'), // The plugin stores the PayPal-PaymentId as temporaryId.
            transactionId = record.get('transactionId'), // The plugin stores the PayPal-PaymentId as temporaryId.
            paymentMethodId = record.get('paymentId'),
            window = this.getWindow();

        window.changeSidebar(window.sidebarNames.SIDEBAR_V2);
        window.getCurrentSidebar().setLoading(true);

        this.record = record;

        this.getOrderById(paymentId, paymentMethodId, transactionId, Ext.bind(this.orderDetailsV2Callback, this));

        window.getCurrentSidebar().getShopwareOrderTab().loadRecord(record);
    },

    /**
     * @param { String } id
     * @param { Number } paymentMethodId
     * @param { String } transactionId
     * @param { Function } callback
     */
    getOrderById: function(id, paymentMethodId, transactionId, callback) {
        Ext.Ajax.request({
            url: this.urls.orderDetails,
            params: {
                id: id,
                paymentMethodId: paymentMethodId,
                transactionId: transactionId,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * @param request { Object }
     * @param opts { Object }
     * @param response { Object }
     */
    orderDetailsV2Callback: function(request, opts, response) {
        var responseObject = Ext.JSON.decode(response.responseText);

        if (responseObject.success === false) {
            this.showError(responseObject.message);
            return;
        }

        this.currentOrderData = responseObject.data;
        this.showV2Data(responseObject.data);
    },

    /**
     * @param paypalOrderData { Object }
     */
    showV2Data: function(paypalOrderData) {
        var window = this.getWindow(),
            sideBar = window.getCurrentSidebar();

        sideBar.setOrderData(paypalOrderData);
        sideBar.setLoading(false);
    },

    /**
     * @returns { Ext.data.Model }
     */
    getRecord: function() {
        return this.record;
    },

    /**
     * @returns { Number }
     */
    getCurrentShopId: function() {
        return this.getRecord().get('languageIso');
    },

    onCaptureButtonClick: function() {
        Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.windows.CaptureWindow', {
            currentOrderData: this.currentOrderData
        }).show();
    },

    onCancelAuthorization: function() {
        Ext.Msg.show({
            title: '{s name="cancelAuthorization/title/cancelPayment"}Cancel payment?{/s}',
            msg: '{s name="cancelAuthorization/message/cancelPayment"}Do you really want to cancel this payment?{/s}',
            buttons: Ext.Msg.OKCANCEL,
            icon: Ext.Msg.QUESTION,
            fn: Ext.bind(this.cancelAuthorization, this),
        });
    },

    /**
     * @param buttonValue { String }
     */
    cancelAuthorization: function(buttonValue) {
        if (buttonValue !== 'ok') {
            return;
        }

        var window = this.getWindow(),
            parameter = {
                authorizationId: this.currentOrderData.purchase_units[0].payments.authorizations[0].id
            };

        window.setLoading(true);
        this.callAjax(this.urls.cancelAuthorization, parameter, Ext.bind(this.afterCancelAuthorization, this, [window], true));
    },

    /**
     * @param response { Object }
     * @param request { Object }
     * @param window { Ext.window.Window }
     */
    afterCancelAuthorization: function(response, request, window) {
        try {
            var responseObject = Ext.decode(response.responseText);
        } catch (exception) {
            this.showError(exception.message);
        }

        if (responseObject.success !== true) {
            this.showError(['Errorcode: ', responseObject.code, '<br>', responseObject.message].join(''));
            window.setLoading(false);
            return;
        }

        window.setLoading(false);
        this.onSelectGridRecord(Ext.emptyFn, this.record);
    },

    onRefundButtonClick: function() {
        var paymentHistoryGrid = this.getPaymentHistoryGrid(),
            paymentType = paymentHistoryGrid.getCurrentPaymentType();

        switch (paymentType) {
            case this.paymentTypes.authorization.key:
                Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.windows.RefundAuthorizeWindow', {
                    currentOrderData: this.currentOrderData
                }).show();
                break;
            case this.paymentTypes.capture.key:
                Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.windows.RefundCaptureWindow', {
                    currentOrderData: this.currentOrderData
                }).show();
                break;
        }
    },

    /**
     * @param window { Ext.window.Window }
     */
    onExecuteCapture: function(window) {
        var form = window.getForm();

        if (!form.isValid()) {
            return;
        }

        window.setLoading(true);
        this.callAjax(this.urls.captureOrder, form.getValues(), Ext.bind(this.valuesResponse, this, [window], true));
    },

    /**
     * @param window { Ext.window.Window }
     */
    onExecuteRefund: function(window) {
        var form = window.getForm(),
            formValues;

        if (!form.isValid()) {
            return;
        }

        formValues = form.getValues();
        formValues.paypalOrderId = this.currentOrderData.id;

        window.setLoading(true);
        this.callAjax(this.urls.refundOrder, formValues, Ext.bind(this.valuesResponse, this, [window], true));
    },

    /**
     * @param response { Object }
     * @param request { Object }
     * @param window { Ext.window.Window }
     */
    valuesResponse: function(response, request, window) {
        try {
            var responseObject = Ext.decode(response.responseText);
        } catch (exception) {
            this.showError(exception.message);
            window.setLoading(false);
            return;
        }

        if (responseObject.success !== true) {
            this.showError(['Errorcode: ', responseObject.code, '<br>', responseObject.message].join(''));
            window.setLoading(false);
            return;
        }

        this.onSelectGridRecord(Ext.emptyFn, this.record);

        window.close();
        window.destroy();
    },

    /**
     * @param selectedItem { Object }
     */
    onHistoryGridChange: function(selectedItem) {
        this.getPaymentDetailFieldset().setPaymentDetails(selectedItem);
    },

    callAjax: function(url, parameter, callback) {
        Ext.Ajax.request({
            url: url,
            params: parameter,
            success: callback,
        });
    },

    showError: function(message) {
        Ext.Msg.show({
            title: 'ERROR',
            msg: message,
            buttons: Ext.Msg.OK,
            icon: Ext.Msg.ERROR
        });
    },
});
// {/block}
