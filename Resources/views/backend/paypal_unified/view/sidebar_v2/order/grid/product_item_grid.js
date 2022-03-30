// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/grid/ProductItemGrid"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.grid.ProductItemGrid', {
    extend: 'Ext.grid.Panel',

    width: '100%',

    initComponent: function() {
        this.createStore([]);
        this.columns = this.createColumns();

        this.callParent(arguments);
    },

    /**
     * @return { Array }
     */
    createColumns: function() {
        return [{
            text: '{s name="paypalUnified/V2/product"}Product{/s}',
            dataIndex: 'name',
            flex: 1,
        }, {
            text: '{s name="paypalUnified/V2/number"}Number{/s}',
            dataIndex: 'sku',
            flex: 1,
        }, {
            text: '{s name="paypalUnified/V2/price"}Price{/s}',
            dataIndex: 'unit_amount.value',
            flex: 1,
            renderer: this.priceRenderer
        }, {
            text: '{s name="paypalUnified/V2/productAmount"}Amount{/s}',
            dataIndex: 'quantity',
            width: 50,
        }]
    },

    /**
     * @param { Array } storeData
     */
    setStore: function(storeData) {
        var store = this.createStore(storeData);

        this.reconfigure(store, this.createColumns());
    },

    /**
     * @param { Array } storeData
     */
    createStore: function(storeData) {
        return Ext.create('Ext.data.Store', {
            fields: ['name', 'sku', 'unit_amount.value', 'quantity'],
            data: storeData
        });
    },

    /**
     * @param { Number } value
     * @param { Object } style
     * @param { Ext.data.Model } record
     * @return { string }
     */
    priceRenderer: function(value, style, record) {
        return [value, record.raw.unit_amount.currency_code].join(' ');
    },
});
// {/block}
