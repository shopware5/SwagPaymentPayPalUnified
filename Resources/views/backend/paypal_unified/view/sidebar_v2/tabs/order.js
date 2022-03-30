// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/tabs/order"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.Order', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.tabs.AbstractTab',
    alias: 'widget.paypal-unified-sidebarV2-order',

    title: '{s name="tabs/title/order"}Order{/s}',

    /**
     * @returns { Array }
     */
    createItems: function() {
        this.detailsContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.order.Details');
        this.customerContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.order.Customer');

        return [
            this.detailsContainer,
            this.customerContainer
        ];
    },

    /**
     * @param { Object } paypalOrderData
     */
    setOrderData: function(paypalOrderData) {
        this.paypalTransactioinTab.setOrderData(paypalOrderData);
    },

    loadRecord: function(record) {
        var customer = record.getCustomer().first().raw,
            orderDetails = record.raw;

        this.detailsContainer.numberField.setValue(orderDetails.number);
        this.detailsContainer.transactionIdField.setValue(orderDetails.transactionId);
        this.detailsContainer.currencyField.setValue(orderDetails.currency);
        this.detailsContainer.invoiceAmountField.setValue(orderDetails.invoiceAmount);
        this.detailsContainer.orderTimeField.setValue(orderDetails.orderTime);
        this.detailsContainer.orderStatusField.setValue(orderDetails.orderStatus.name);
        this.detailsContainer.paymentStatusField.setValue(orderDetails.paymentStatus.name);

        this.customerContainer.salutationField.setValue(customer.salutation);
        this.customerContainer.firstnameField.setValue(customer.firstname);
        this.customerContainer.lastnameField.setValue(customer.lastname);
        this.customerContainer.emailField.setValue(customer.email);
        this.customerContainer.groupKeyField.setValue(customer.groupKey);
    },
});
// {/block}
