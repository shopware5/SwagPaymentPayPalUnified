// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/grid/PaymentHistoryGrid"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.grid.PaymentHistoryGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.paypal-unified-order-payment-history-grid-v2',

    width: '100%',

    paymentTypes: Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.PaymentTypes.PaymentTypes'),
    dateTimeFormatter: Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.fields.DateTimeFieldFormatter'),

    initComponent: function() {
        this.createStore([]);
        this.columns = this.createColumns();
        this.createChangeEvent();

        this.callParent(arguments);
    },

    /**
     * @return { Array }
     */
    createColumns: function() {
        return [{
            text: '{s name="paypalUnified/V2/type"}Type{/s}',
            dataIndex: 'type',
            flex: 1,
            renderer: this.typeRenderer
        }, {
            text: '{s name="paypalUnified/V2/amount"}Amount{/s}',
            dataIndex: 'amount.value',
            flex: 1,
            renderer: this.priceRenderer
        }, {
            text: '{s name="paypalUnified/V2/createTime"}Created{/s}',
            dataIndex: 'create_time',
            flex: 1,
            renderer: this.dateTimeFormatter.format
        }, {
            text: '{s name="paypalUnified/V2/status"}Status{/s}',
            dataIndex: 'status',
            flex: 1,
        }]
    },

    /**
     * @param storeData { Array }
     */
    setStore: function(storeData) {
        var store = this.createStore(storeData);

        this.reconfigure(store, this.createColumns());
    },

    /**
     * @param storeData { Array }
     */
    createStore: function(storeData) {
        return Ext.create('Ext.data.Store', {
            fields: ['type', 'amount.value', 'create_time', 'status'],
            data: storeData
        });
    },

    /**
     * @param value { Number }
     * @param style { Object }
     * @param record { Ext.data.Model }
     *
     * @return { string }
     */
    priceRenderer: function(value, style, record) {
        return [value, record.raw.amount.currency_code].join(' ');
    },

    /**
     * @param value { string }
     *
     * @return { string }
     */
    typeRenderer: function(value) {
        return this.paymentTypes[value].label;
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function(paypalOrderData) {
        var me = this,
            purchaseUnit = paypalOrderData.purchase_units[0],
            storeData = [];

        Ext.each(purchaseUnit.payments.authorizations, function(authorization) {
            authorization.type = me.paymentTypes.authorization.key;
            storeData.push(authorization);
        });

        Ext.each(purchaseUnit.payments.captures, function(capture) {
            capture.type = me.paymentTypes.capture.key;
            storeData.push(capture);
        });

        Ext.each(purchaseUnit.payments.refunds, function(refund) {
            refund.type = me.paymentTypes.refund.key;
            storeData.push(refund);
        });

        this.setStore(storeData);
        this.getSelectionModel().select(0);

        this.currentPaymentType = this.getSelectionModel().getSelection()[0].get('type');
    },

    createChangeEvent: function() {
        this.getSelectionModel().on('selectionchange', Ext.bind(this.onSelectionChange, this));
    },

    /**
     * @param grid { Ext.grid.Panel }
     * @param selectedRecords { Array }
     */
    onSelectionChange: function(grid, selectedRecords) {
        if (!selectedRecords.length) {
            return;
        }

        this.fireEvent('onSelectionChange', selectedRecords[0]);
    },

    /**
     * @return { String }
     */
    getCurrentPaymentType: function() {
        return this.currentPaymentType;
    },
});
// {/block}
