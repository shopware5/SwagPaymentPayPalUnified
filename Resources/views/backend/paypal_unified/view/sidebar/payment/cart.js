//{namespace name="backend/paypal_unified/sidebar/payment/cart"}
//{block name="backend/paypal_unified/sidebar/payment/cart"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.payment.Cart', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.paypal-unified-sidebar-payment-cart',

    anchor: '100%',
    margin: 5,

    border: false,

    initComponent: function () {
        var me = this;

        me.columns = me.createColumns();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createColumns: function () {
        var me = this;

        return [
            { text: '{s name="column/name"}Name{/s}', dataIndex: 'name', flex: 3 },
            { text: '{s name="column/number"}Number{/s}', dataIndex: 'sku', flex: 2 },
            { text: '{s name="column/price"}Price{/s}', dataIndex: 'price', flex: 2, renderer: me.renderAmount },
            { text: '{s name="column/quantity"}Quantity{/s}', dataIndex: 'quantity', flex: 1 }
        ];
    },

    /**
     * @param { String } value
     * @param { Object } style
     * @param { Ext.data.Model } record
     * @returns { String }
     */
    renderAmount: function (value, style, record) {
        return Ext.util.Format.currency(value) + ' ' + record.get('currency');
    }
});
//{/block}