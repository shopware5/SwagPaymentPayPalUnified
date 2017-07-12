// {namespace name="backend/paypal_unified/controller/main}
// {block name="backend/paypal_unified/controller/history"}
Ext.define('Shopware.apps.PaypalUnified.controller.History', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'sidebar', selector: 'paypal-unified-overview-sidebar' }
    ],

    /**
     * @type { Shopware.apps.PaypalUnified.controller.Api }
     */
    apiController: null,

    init: function () {
        this.apiController = this.getController('Api');

        this.createComponentControl();
        this.callParent(arguments);
    },

    createComponentControl: function () {
        this.control({
            'paypal-unified-sidebar-history-grid': {
                'select': this.onSelectHistoryGridRecord
            }
        });
    },

    /**
     * @param { Ext.selection.RowModel } element
     * @param { Ext.data.Model } record
     */
    onSelectHistoryGridRecord: function (element, record) {
        var id = record.get('id'),
            type = record.get('type');

        this.getSidebar().setLoading(true);
        this.getSidebar().historyTab.loadRecord(null);

        switch (type) {
            case 'sale':
                this.apiController.getSaleDetails(id, Ext.bind(this.saleDetailsCallback, this));
                break;
            case 'refund':
                this.apiController.getRefundDetails(id, Ext.bind(this.refundDetailsCallback, this));
                break;
            case 'capture':
                this.apiController.getCaptureDetails(id, Ext.bind(this.captureDetailsCallback, this));
                break;
            case 'order':
                this.apiController.getOrderDetails(id, Ext.bind(this.orderDetailsCallback, this));
                break;
            case 'authorization':
                this.apiController.getAuthorizationDetails(id, Ext.bind(this.authorizationDetailsCallback, this));
                break;
        }
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    saleDetailsCallback: function (options, success, response) {
        this.getSidebar().setLoading(false);

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/error}An error occurred while requesting the PayPal payment details{/s}');
            return;
        }

        var historyTab = this.getSidebar().historyTab,
            responseObject = Ext.JSON.decode(response.responseText),
            details;

        if (responseObject.success) {
            details = responseObject.details;
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.Sale', details));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', details.amount));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.TransactionFee', details.transaction_fee));

            this.updateFields(details);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', responseObject.message);
        }

        historyTab.detailsContainer.enable();
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    refundDetailsCallback: function (options, success, response) {
        this.getSidebar().setLoading(false);

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/error}An error occurred while requesting the PayPal payment details{/s}');
            return;
        }

        var historyTab = this.getSidebar().historyTab,
            responseObject = Ext.JSON.decode(response.responseText),
            details;

        if (responseObject.success) {
            details = responseObject.details;
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.Refund', details));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', details.amount));

            this.updateFields(details);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', responseObject.message);
        }

        historyTab.detailsContainer.enable();
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    captureDetailsCallback: function (options, success, response) {
        this.getSidebar().setLoading(false);

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/error}An error occurred while requesting the PayPal payment details{/s}');
            return;
        }

        var historyTab = this.getSidebar().historyTab,
            responseObject = Ext.JSON.decode(response.responseText),
            details;

        if (responseObject.success) {
            details = responseObject.details;

            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.Capture', details));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', details.amount));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.TransactionFee', details.transaction_fee));

            this.updateFields(details);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', responseObject.message);
        }

        historyTab.detailsContainer.enable();
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    orderDetailsCallback: function (options, success, response) {
        this.getSidebar().setLoading(false);

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/error}An error occurred while requesting the PayPal payment details{/s}');
            return;
        }

        var historyTab = this.getSidebar().historyTab,
            responseObject = Ext.JSON.decode(response.responseText),
            details;

        if (responseObject.success) {
            details = responseObject.details;
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.Order', details));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', details.amount));

            this.updateFields(details);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', responseObject.message);
        }

        historyTab.detailsContainer.enable();
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    authorizationDetailsCallback: function (options, success, response) {
        this.getSidebar().setLoading(false);

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/error}An error occurred while requesting the PayPal payment details{/s}');
            return;
        }

        var historyTab = this.getSidebar().historyTab,
            responseObject = Ext.JSON.decode(response.responseText),
            details;

        if (responseObject.success) {
            details = responseObject.details;
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.Authorization', details));
            historyTab.loadRecord(Ext.create('Shopware.apps.PaypalUnified.model.PaymentAmount', details.amount));

            this.updateFields(details);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', responseObject.message);
        }

        historyTab.detailsContainer.enable();
    },

    /**
     * A helper functions that improves the usability by formatting different fields
     * after the records were applied.
     *
     * @param { Object } details
     */
    updateFields: function (details) {
        var historyTab = this.getSidebar().historyTab;

        // Update time
        historyTab.down('#totalAmount').setValue(Ext.util.Format.currency(details.amount.total) + ' ' + details.amount.currency);
        historyTab.down('#updateTime').setValue(Ext.util.Format.date(details.update_time, 'd.m.Y H:i:s'));
        historyTab.down('#createTime').setValue(Ext.util.Format.date(details.create_time, 'd.m.Y H:i:s'));

        if (details.valid_until) {
            historyTab.down('#validUntil').setValue(Ext.util.Format.date(details.valid_until, 'd.m.Y H:i:s'));
        }

        if (details.transaction_fee) {
            historyTab.down('#transactionFee').setValue(Ext.util.Format.currency(details.transaction_fee.value) + ' ' + details.transaction_fee.currency);
        }
    }
});
// {/block}
