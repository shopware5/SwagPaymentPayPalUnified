// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebar_v2/capture_refund/Toolbar"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.captureRefund.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.paypal-unified-refund-Toolbar',
    dock: 'bottom',
    ui: 'shopware-ui',

    window: null,

    initComponent: function() {
        this.items = [
            this.createCancelButton(),
            '->',
            this.createSaveButton()
        ];

        this.callParent(arguments);
    },

    /**
     * @return { Ext.button.Button }
     */
    createCancelButton: function() {
        return Ext.create('Ext.button.Button', {
            text: '{s name="paypalUnified/V2/cancel"}Cancel{/s}',
            cls: 'secondary',
            handler: Ext.bind(this.onCancel, this),
        })
    },

    /**
     * @return { Ext.button.Button }
     */
    createSaveButton: function() {
        return Ext.create('Ext.button.Button', {
            text: '{s name="paypalUnified/V2/execute"}Execute{/s}',
            cls: 'primary',
            handler: Ext.bind(this.onSave, this),
        });
    },

    onCancel: function() {
        this.window.close();
        this.window.destroy();
    },

    onSave: function() {
        this.window.fireEvent('executeAction', this.window);
    },

    /**
     * @param window { Ext.window.Window }
     */
    setWindow: function(window) {
        this.window = window;
    }
});
// {/block}
