//{namespace name="backend/paypal_unified/sidebar/refund/refund_button"}
//{block name="backend/paypal_unified/sidebar/refund/refund_button"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.refund.RefundButton', {
    extend: 'Shopware.apps.Base.view.element.Button',
    alias: 'widget.paypal-unified-sidebar-refund-refund-button',

    anchor: '100%',
    margin: 5,
    cls: 'primary',
    text: '{s name="title"}Create a new refund{/s}',
    initComponent: function () {
        var me = this;

        me.callParent(arguments);
    }
});
//{/block}