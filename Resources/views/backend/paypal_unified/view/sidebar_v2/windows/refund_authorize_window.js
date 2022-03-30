// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebar_v2/action_windows/RefundAuthorizeWindow"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.windows.RefundAuthorizeWindow', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.windows.AbstractWindow',
    alias: 'widget.paypal-unified-v2-actions-refund-authorize-window',
    title: '{s name="refundAuthorizeWindow/title"}New refund{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        return [
            this.createCaptureSelection(),
            this.createAmountField(),
            this.createNoteField(),
            this.createCurrencyField(),
        ];
    },

    /**
     * @return { Ext.form.field.ComboBox }
     */
    createCaptureSelection: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'captureId',
            fieldLabel: '{s name="refundAuthorizeWindow/field/selectEntry"}Select entry{/s}',
            store: this.capturesStore(),
            displayField: 'name',
            valueField: 'id',
            allowBlank: false,
        });
    },

    /**
     * @return { Ext.data.Store }
     */
    capturesStore: function() {
        var storeData = [],
            captures = this.currentOrderData.purchase_units[0].payments.captures;

        Ext.each(captures, function(capture) {
            storeData.push({
                name: [capture.create_time, '(', capture.amount.value, capture.amount.currency_code, ')', '-', capture.id].join(' '),
                id: capture.id
            });
        });

        return Ext.create('Ext.data.Store', {
            fields: ['name', 'id'],
            data: storeData,
        });
    },

    /**
     * @return { Ext.form.field.TextArea }
     */
    createNoteField: function() {
        return Ext.create('Ext.form.field.TextArea', {
            name: 'note',
            fieldLabel: '{s name="refundAuthorizeWindow/field/note"}Note{/s}',
            allowBlank: false,
        });
    },

    /**
     * @return { String }
     */
    getCurrency: function () {
        return this.currentOrderData.purchase_units[0].payments.authorizations[0].amount.currency_code;
    },
});
// {/block}
