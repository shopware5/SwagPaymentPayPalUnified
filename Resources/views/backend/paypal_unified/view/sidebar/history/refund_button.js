// {namespace name="backend/paypal_unified/sidebar/history/refund_button"}
// {block name="backend/paypal_unified/sidebar/history/refund_button"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.history.RefundButton', {
    extend: 'Shopware.apps.Base.view.element.Button',
    alias: 'widget.paypal-unified-sidebar-history-refund-button',

    itemId: 'refundButton',
    anchor: '100%',
    margin: 5,
    cls: 'primary',
    text: '{s name="title"}Create a new refund{/s}',
    disabled: true,

    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
// {/block}
