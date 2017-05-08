// {namespace name="backend/paypal_unified/sidebar/history/grid"}
// {block name="backend/paypal_unified/sidebar/history/grid"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.history.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.paypal-unified-sidebar-history-grid',

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
            { text: '{s name="column/type"}Type{/s}', dataIndex: 'type', flex: 2, renderer: me.renderType },
            { text: '{s name="column/amount"}Amount{/s}', dataIndex: 'amount', flex: 2, renderer: me.renderAmount },
            { text: '{s name="column/createTime"}Create time{/s}', dataIndex: 'create_time', flex: 2, renderer: me.renderDateTime },
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
    },

    /**
     * @param { String } value
     */
    renderType: function (value) {
        switch (value) {
            case 'authorization':
                return '{s name="type/authorization"}Authorization{/s}';
            case 'sale':
                return '{s name="type/sale"}Sale{/s}';
            case 'refund':
                return '{s name="type/refund"}Refund{/s}';
            case 'capture':
                return '{s name="type/capture"}Capture{/s}';
            case 'order':
                return '{s name="type/order"}Order{/s}';
            default:
                return value;
        }
    }
});
// {/block}
