// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/payment_history/refund_button"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paymentHistory.RefundButton', {
    extend: 'Ext.button.Button',
    alias: 'widget.paypal-unified-sidebar-order-fieldset-paymentHistory-refundButton',

    itemId: 'refundButtonV2',
    anchor: '100%',
    margin: 5,
    cls: 'primary',
    text: '{s name="refundButton/text"}Create a new refund{/s}',

    disabled: true,

    handler: function () {
        this.fireEvent('refundButtonClick');
    },
});
// {/block}
