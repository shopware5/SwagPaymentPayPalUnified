//{namespace name="backend/paypal_unified/sidebar/refund/sales"}
//{block name="backend/paypal_unified/sidebar/refund/sales"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.refund.Sales', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.paypal-unified-sidebar-refund-sales',

    anchor: '100%',
    margin: 5,

    border: false,
    sortableColumns: false,

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
            { text: '{s name="column/amount"}Amount{/s}', dataIndex: 'amount', flex: 2, renderer: me.renderAmount },
            { text: '{s name="column/createTime"}Create time{/s}', dataIndex: 'create_time', flex: 2, renderer: me.renderDateTime },
            { text: '{s name="column/updateTime"}Update time{/s}', dataIndex: 'update_time', flex: 2, renderer: me.renderDateTime },
            { text: '{s name="column/state"}State{/s}', dataIndex: 'state', flex: 2 }
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
    },

    /**
     * @param { String } value
     */
    renderDateTime: function (value) {
        return Ext.util.Format.date(value, 'd.m.Y');
    }
});
//{/block}