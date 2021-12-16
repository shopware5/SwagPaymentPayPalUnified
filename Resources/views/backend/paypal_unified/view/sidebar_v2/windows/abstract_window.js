// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebar_v2/action_windows/AbstractWindow"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.windows.AbstractWindow', {
    extend: 'Ext.window.Window',
    width: 400,
    layout: 'fit',
    bodyPadding: 5,
    modal: true,
    bodyStyle: {
        background: '#f0f2f4'
    },

    initComponent: function() {
        this.items = this.createFormPanel();
        this.dockedItems = this.createToolbar();

        this.callParent(arguments);
    },

    /**
     * @return { Ext.form.Panel }
     */
    createFormPanel: function() {
        this.form = Ext.create('Ext.form.Panel', {
            border: false,
            layout: 'anchor',
            items: this.createItems(),
            defaults: {
                anchor: '100%',
                labelWidth: 130,
            },
        });

        return this.form;
    },

    /**
     * @return { Shopware.apps.PaypalUnified.view.sidebarV2.captureRefund.Toolbar }
     */
    createToolbar: function() {
        return Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.captureRefund.Toolbar', {
            window: this
        });
    },

    /**
     * @return { Ext.form.field.Number }
     */
    createAmountField: function() {
        this.amountField = Ext.create('Ext.form.field.Number', {
            name: 'amount',
            fieldLabel: '{s name="paypalUnified/V2/amount"}Amount{/s}',
            minValue: 0.01,
            allowBlank: false,
        });

        return this.amountField;
    },

    /**
     * @return { Ext.form.field.Number }
     */
    createMaxAmountField: function() {
        this.maxAmountField = Ext.create('Ext.form.field.Number', {
            name: 'maxCaptureAmount',
            fieldLabel: '{s name="paypalUnified/V2/maxAmount"}Max amount{/s}',
            readOnly: true,
            value: this.calculateMaxAmount(),
        });

        return this.maxAmountField;
    },

    /**
     * @return { Ext.form.field.Text }
     */
    createCurrencyField: function() {
        this.currencyField = Ext.create('Ext.form.field.Text', {
            name: 'currency',
            hidden: true,
            value: this.getCurrency(),
        });

        return this.currencyField;
    },

    /**
     * @return { Ext.form.Panel }
     */
    getForm: function() {
        return this.form.form;
    },

    /**
     * @return { Array }
     */
    createItems: function () {
        throw new Error('Method "createItems" is not implemented');
    },

    /**
     * @return { string }
     */
    getCurrency: function () {
        throw new Error('Method "getCurrency" is not implemented');
    },
});
// {/block}

