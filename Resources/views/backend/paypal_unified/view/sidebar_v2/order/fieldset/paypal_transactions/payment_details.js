// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/paypal_transactions/PaymentDetails"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.PaymentDetails', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.AbstractFieldset',
    alias: 'widget.paypal-unified-sidebarV2-order-fieldset-PaypalTransactions.PaymentDetails',
    title: '{s name="fieldset/PaymentDetails/title"}Payment details{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        this.orderId = this.fieldFactory.createField('orderId');
        this.intent = this.fieldFactory.createField('intent');
        this.status = this.fieldFactory.createField('status');
        this.createTime = this.fieldFactory.createField('createTime');
        this.updateTime = this.fieldFactory.createField('updateTime');

        return [
            this.orderId,
            this.intent,
            this.status,
            this.createTime,
            this.updateTime,
        ];
    },

    /**
     * @param { Object } paypalOrderData
     */
    setOrderData: function (paypalOrderData) {
        this.orderId.setValue(paypalOrderData.id);
        this.intent.setValue(paypalOrderData.intent);
        this.status.setValue(paypalOrderData.status);
        this.createTime.setValue(this.dateTimeFormatter.format(paypalOrderData.create_time));
        this.updateTime.setValue(this.dateTimeFormatter.format(paypalOrderData.update_time));
    },
});
// {/block}
