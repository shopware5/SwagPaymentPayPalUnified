// {block name="backend/paypal_unified/controller/api"}
Ext.define('Shopware.apps.PaypalUnified.controller.Api', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Shopware.apps.PaypalUnified.controller.Main }
     */
    windowController: null,

    /**
     * @type { Object }
     */
    urls: {
        paymentDetails: '{url module=backend controller=PaypalUnified action=paymentDetails}',
        captureOrder: '{url module=backend controller=PaypalUnified action=captureOrder}',
        captureAuthorization: '{url module=backend controller=PaypalUnified action=captureAuthorization}',
        voidOrder: '{url module=backend controller=PaypalUnified action=voidOrder}',
        voidAuthorization: '{url module=backend controller=PaypalUnified action=voidAuthorization}',
        refundCapture: '{url module=backend controller=PaypalUnified action=refundCapture}',
        refundSale: '{url module=backend controller=PaypalUnified action=refundSale}',
        refundDetails: '{url module=backend controller=PaypalUnified action=refundDetails}',
        saleDetails: '{url module=backend controller=PaypalUnified action=saleDetails}',
        captureDetails: '{url module=backend controller=PaypalUnified action=captureDetails}',
        orderDetails: '{url module=backend controller=PaypalUnified action=orderDetails}',
        authorizationDetails: '{url module=backend controller=PaypalUnified action=authorizationDetails}'
    },

    /**
     * We need a window controller in order to receive required information like "shopId".
     */
    init: function () {
        // The window controller is required to gather information about the
        // selected order.
        this.windowController = this.getController('Main');
        this.callParent(arguments);
    },

    /**
     * Requests the specified payment from the paypal API and triggers the
     * callback function after it was received.
     *
     * @param { String } id - The id of the payment that should be received.
     * @param { Number } paymentMethodId - The id of the payment method.
     * @param { String } transactionId - The transaction id of the order
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    getPaymentById: function (id, paymentMethodId, transactionId, callback) {
        Ext.Ajax.request({
            url: this.urls.paymentDetails,
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
     * Voids the order and triggers the callback function after the
     * result was received.
     *
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    voidOrder: function (callback) {
        Ext.Ajax.request({
            url: this.urls.voidOrder,
            params: {
                id: this.getDetails().order.id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * Voids the authorization and triggers the callback function after the
     * result was received.
     *
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    voidAuthorization: function (callback) {
        Ext.Ajax.request({
            url: this.urls.voidAuthorization,
            params: {
                id: this.getDetails().authorization.id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * Captures an order using the specified amount and triggers the callback
     * function after a result was received.
     *
     * @param { Numeric } amount - The amount that should be captured.
     * @param { Boolean } isFinal - A flag indicating if another capture is possible afterwards.
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    captureOrder: function (amount, isFinal, callback) {
        Ext.Ajax.request({
            url: this.urls.captureOrder,
            params: {
                id: this.getDetails().order.id,
                currency: this.getPaymentCurrency(),
                shopId: this.getCurrentShopId(),
                amount: amount,
                isFinal: isFinal
            },
            callback: callback
        });
    },

    /**
     * Captures the authorization using the specified amount and triggers the callback
     * function after a result was received.
     *
     * @param { Numeric } amount - The amount that should be captured.
     * @param { Boolean } isFinal - A flag indicating if another capture is possible afterwards.
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    captureAuthorization: function (amount, isFinal, callback) {
        Ext.Ajax.request({
            url: this.urls.captureAuthorization,
            params: {
                id: this.getDetails().authorization.id,
                currency: this.getPaymentCurrency(),
                shopId: this.getCurrentShopId(),
                amount: amount,
                isFinal: isFinal
            },
            callback: callback
        });
    },

    /**
     * Refunds a sale using the specified amount and invoice number and triggers
     * the callback function after a result was received.
     *
     * @param { Numeric } amount - The amount that should be refunded.
     * @param { String } invoiceNumber - The invoice number for this refund.
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    refundSale: function (amount, invoiceNumber, callback) {
        var id = this.getDetails().legacy ? this.getDetails().payment.id : this.getDetails().sale.id;

        Ext.Ajax.request({
            url: this.urls.refundSale,
            params: {
                id: id,
                shopId: this.getCurrentShopId(),
                amount: amount,
                invoiceNumber: invoiceNumber,
                currency: this.getPaymentCurrency()
            },
            callback: callback
        });
    },

    /**
     * Refunds a capture using the specified amount and notice and triggers
     * the callback function after a result was received.
     *
     * @param { String } id - The id of the capture that should be refunded.
     * @param { Numeric } amount - The amount that should be refunded.
     * @param { String } note - The notice for this refund.
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    refundCapture: function (id, amount, note, callback) {
        Ext.Ajax.request({
            url: this.urls.refundCapture,
            params: {
                id: id,
                shopId: this.getCurrentShopId(),
                amount: amount,
                note: note,
                currency: this.getPaymentCurrency()
            },
            callback: callback
        });
    },

    /**
     * Requests the specified refund details from the paypal API and triggers the
     * callback function after it was received.
     *
     * @param { String } id - The id of the refund
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    getRefundDetails: function (id, callback) {
        Ext.Ajax.request({
            url: this.urls.refundDetails,
            params: {
                id: id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * Requests the specified sale details from the paypal API and triggers the
     * callback function after it was received.
     *
     * @param { String } id - The id of the refund
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    getSaleDetails: function (id, callback) {
        Ext.Ajax.request({
            url: this.urls.saleDetails,
            params: {
                id: id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * Requests the specified capture details from the paypal API and triggers the
     * callback function after it was received.
     *
     * @param { String } id - The id of the refund
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    getCaptureDetails: function (id, callback) {
        Ext.Ajax.request({
            url: this.urls.captureDetails,
            params: {
                id: id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * Requests the specified order details from the paypal API and triggers the
     * callback function after it was received.
     *
     * @param { String } id - The id of the refund
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    getOrderDetails: function (id, callback) {
        Ext.Ajax.request({
            url: this.urls.orderDetails,
            params: {
                id: id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * Requests the specified authorization details from the paypal API and triggers the
     * callback function after it was received.
     *
     * @param { String } id - The id of the refund
     * @param { Function } callback - This function will be triggered after the result was received.
     */
    getAuthorizationDetails: function (id, callback) {
        Ext.Ajax.request({
            url: this.urls.authorizationDetails,
            params: {
                id: id,
                shopId: this.getCurrentShopId()
            },
            callback: callback
        });
    },

    /**
     * @returns { Object }
     */
    getDetails: function () {
        return this.windowController.getDetails();
    },

    /**
     * @returns { Ext.data.Model }
     */
    getRecord: function () {
        return this.windowController.getRecord();
    },

    /**
     * A helper function that returns the Id of the currently selected shop.
     *
     * @returns { Numeric } - The shop id
     */
    getCurrentShopId: function () {
        return this.getRecord().get('languageIso');
    },

    /**
     * A helper function that returns the initial currency in which this
     * order has been payed.
     *
     * @returns { String } - The payment's currency ISO (e.g EUR)
     */
    getPaymentCurrency: function () {
        return this.getDetails().payment.transactions[0].amount.currency;
    }
});
// {/block}
