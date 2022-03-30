// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebar_v2/action_windows/RefundCaptureWindow"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.windows.RefundCaptureWindow', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.windows.AbstractWindow',
    alias: 'widget.paypal-unified-v2-actions-refund-capture-window',
    title: '{s name="refundAuthorizeWindow/title"}New refund{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        return [
            this.createMaxAmountField(),
            this.createAmountField(),
            this.createAutoTotalAmountField(),
            this.createBookingNumberField(),
            this.createCurrencyField(),
            this.createCaptureIdField(),
        ];
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createAutoTotalAmountField: function() {
        var me = this;

        this.autoTotalAmountField = Ext.create('Ext.form.field.Checkbox', {
            name: 'autoTotalAmount',
            fieldLabel: '{s name="paypalUnified/V2/totalAmount"}Total amount{/s}',
            inputValue: true,
            uncheckedValue: false,
            listeners: {
                change: function(checkbox, newValue) {
                    if (newValue === false) {
                        me.amountField.setValue(null);
                        return;
                    }

                    me.amountField.setValue(me.calculateMaxAmount());
                },
            },
        });

        return this.autoTotalAmountField;
    },

    /**
     * @return { Ext.form.field.Text }
     */
    createBookingNumberField: function() {
        this.bookingNumberFiedl = Ext.create('Ext.form.field.Text', {
            name: 'bookingNumber',
            fieldLabel: '{s name="refundCaptureWindow/field/bookingNumber"}Booking number{/s}',
        });

        return this.bookingNumberFiedl;
    },

    /**
     * @return { Ext.form.field.Text }
     */
    createCaptureIdField: function() {
        this.captureIdField = Ext.create('Ext.form.field.Text', {
            name: 'captureId',
            hidden: true,
            value: this.getCaptureId(),
        });

        return this.captureIdField;
    },

    /**
     * @return { String }
     */
    getCaptureId: function() {
        return this.currentOrderData.purchase_units[0].payments.captures[0].id;
    },

    /**
     * @return { Number }
     */
    calculateMaxAmount: function() {
        var captures = this.currentOrderData.purchase_units[0].payments.captures,
            totalAmount = 0.0;

        Ext.each(captures, function(capture) {
            totalAmount += capture.amount.value;
        });

        return totalAmount;
    },

    /**
     * @return { String }
     */
    getCurrency: function() {
        return this.currentOrderData.purchase_units[0].amount.currency_code;
    },
});
// {/block}
