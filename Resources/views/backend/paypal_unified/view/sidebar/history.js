// {namespace name="backend/paypal_unified/sidebar/history"}
// {block name="backend/paypal_unified/sidebar/history"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.History', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebar-refund',
    title: '{s name="title"}Payment history{/s}',
    autoScroll: true,

    style: {
        background: '#EBEDEF'
    },

    bodyPadding: 5,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.history.Grid }
     */
    salesGrid: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.history.Details }
     */
    detailsContainer: null,

    /**
     * @type { Shopware.apps.PaypalUnified.view.sidebar.history.RefundButton }
     */
    refundButton: null,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = [];

        me.salesGrid = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.history.Grid');
        me.refundButton = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.history.RefundButton');
        me.detailsContainer = Ext.create('Shopware.apps.PaypalUnified.view.sidebar.history.Details');
        me.legacyNotice = Shopware.Notification.createBlockMessage('{s name=legacyNotice namespace=backend/paypal_unified/sidebar/payment}Legacy mode: not all information available{/s}', 'notice');

        me.legacyNotice.hide();

        items.push(me.legacyNotice);
        items.push(me.salesGrid);
        items.push(me.detailsContainer);
        items.push(me.refundButton);

        return items;
    },

    /**
     * @param { boolean } visible
     */
    setLegacyWarning: function(visible) {
        var me = this;

        if (visible === true) {
            me.legacyNotice.show();
        } else {
            me.legacyNotice.hide();
        }
    }
});
// {/block}
