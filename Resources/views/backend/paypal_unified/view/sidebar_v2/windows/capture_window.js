// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebar_v2/action_windows/CaptureWindow"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.windows.CaptureWindow', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.windows.AbstractWindow',
    alias: 'widget.paypal-unified-v2-actions-capture-window',
    title: '{s name="captureWindow/title"}Collect payment{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        return [
            this.createMaxAmountField(),
            this.createAmountField(),
            this.createFinalizeField(),
            this.createAuthorizationIdField(),
            this.createCurrencyField(),
        ];
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createFinalizeField: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'finalize',
            fieldLabel: '{s name="captureWindow/field/finalize"}Finalize{/s}',
            inputValue: 1,
            uncheckedValue: 0,
        });
    },

    /**
     * @return { Ext.form.field.Text }
     */
    createAuthorizationIdField: function() {
        this.authorizationIdField = Ext.create('Ext.form.field.Text', {
            name: 'authorizationId',
            hidden: true,
            value: this.getAuthorizationId(),
        });

        return this.authorizationIdField;
    },

    /**
     * @return { Number }
     */
    calculateMaxAmount: function() {
        var amount = this.currentOrderData.purchase_units[0].payments.authorizations[0].amount.value;

        Ext.each(this.currentOrderData.purchase_units[0].payments.captures, function(capture) {
            amount -= capture.amount.value;
        });

        return amount;
    },

    /**
     * @return { String }
     */
    getAuthorizationId: function() {
        return this.currentOrderData.purchase_units[0].payments.authorizations[0].id;
    },

    /**
     * @return { String }
     */
    getCurrency: function () {
        return this.currentOrderData.purchase_units[0].payments.authorizations[0].amount.currency_code;
    },
});
// {/block}
