// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/paypal_transactions/InvoiceAmount"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.InvoiceAmount', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.AbstractFieldset',
    alias: 'widget.paypal-unified-sidebarV2-order-fieldset-PaypalTransactions.InvoiceAmount',
    title: '{s name="fieldset/InvoiceAmount/title"}Invoice amount{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        this.totalAmount = this.fieldFactory.createField('totalAmount');
        this.subtotal = this.fieldFactory.createField('subtotal');
        this.shippingCoasts = this.fieldFactory.createField('shippingCoasts');

        return [
            this.totalAmount,
            this.subtotal,
            this.shippingCoasts
        ];
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function (paypalOrderData) {
        var purchaseUnit = paypalOrderData.purchase_units[0];

        this.totalAmount.setValue([purchaseUnit.amount.value, purchaseUnit.amount.currency_code].join(' '));
        this.subtotal.setValue([purchaseUnit.amount.breakdown.item_total.value, purchaseUnit.amount.breakdown.item_total.currency_code].join(' '));
        this.shippingCoasts.setValue([purchaseUnit.amount.breakdown.shipping.value, purchaseUnit.amount.breakdown.shipping.currency_code].join(' '));
    },
});
// {/block}
