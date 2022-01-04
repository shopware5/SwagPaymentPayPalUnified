// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/payment_history/PaymentDetails"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paymentHistory.PaymentDetails', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.AbstractFieldset',
    alias: 'widget.paypal-unified-sidebar-order-fieldset-payment-history-PaymentDetails',
    title: '{s name="fieldset/Details/title"}Details{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        this.paymentType = this.fieldFactory.createField('paymentType');
        this.paymentStatus = this.fieldFactory.createField('paymentStatus');
        this.paymentId = this.fieldFactory.createField('paymentId');
        this.paymentCustomId = this.fieldFactory.createField('paymentCustomId');
        this.paymentAmount = this.fieldFactory.createField('paymentAmount');
        this.paymentCurrency = this.fieldFactory.createField('paymentCurrency');
        this.paymentCreated = this.fieldFactory.createField('paymentCreated');
        this.paymentUpdated = this.fieldFactory.createField('paymentUpdated');
        this.paymentExpirationTime = this.fieldFactory.createField('paymentExpirationTime');

        return [
            this.paymentId,
            this.paymentStatus,
            this.paymentId,
            this.paymentCustomId,
            this.paymentAmount,
            this.paymentCurrency,
            this.paymentCreated,
            this.paymentUpdated,
            this.paymentExpirationTime,
        ];
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function(paypalOrderData) {
        // Do nothing
    },

    /**
     * @param payment { Ext.data.Model }
     */
    setPaymentDetails: function(payment) {
        this.paymentType.setValue(payment.raw.type);
        this.paymentStatus.setValue(payment.raw.status);
        this.paymentId.setValue(payment.raw.id);
        this.paymentCustomId.setValue(payment.raw.custom_id);
        this.paymentAmount.setValue(payment.raw.amount.value);
        this.paymentCurrency.setValue(payment.raw.amount.currency_code);
        this.paymentCreated.setValue(this.dateTimeFormatter.format(payment.raw.create_time));
        this.paymentUpdated.setValue(this.dateTimeFormatter.format(payment.raw.update_time));
        this.paymentExpirationTime.setValue(this.dateTimeFormatter.format(payment.raw.expiration_time));
    },
});
// {/block}
