// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/tabs/PaypalTransactions"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.PaypalTransactions', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.tabs.AbstractTab',
    alias: 'widget.paypal-unified-sidebarV2-PaypalTransactions',

    title: '{s name="tabs/title/PaypalTransactions"}Paypal transactions{/s}',

    /**
     * @returns { Array }
     */
    createItems: function() {
        var items = [];

        this.productItemGrid = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.grid.ProductItemGrid');
        this.invoiceAmountFieldset = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.InvoiceAmount');
        this.paymentDetailsFieldset = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.PaymentDetails');
        this.payerDetailsFieldset = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.PayerDetails');
        this.shippingAddressFieldset = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.ShippingAddress');

        items.push(this.productItemGrid);
        items.push(this.invoiceAmountFieldset);
        items.push(this.paymentDetailsFieldset);
        items.push(this.payerDetailsFieldset);
        items.push(this.shippingAddressFieldset);

        return items;
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function(paypalOrderData) {
        this.productItemGrid.setStore(paypalOrderData.purchase_units[0].items);
        this.invoiceAmountFieldset.setOrderData(paypalOrderData);
        this.paymentDetailsFieldset.setOrderData(paypalOrderData);
        this.payerDetailsFieldset.setOrderData(paypalOrderData);
        this.shippingAddressFieldset.setOrderData(paypalOrderData);
    },
});
// {/block}
