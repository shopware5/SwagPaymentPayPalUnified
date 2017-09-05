// {namespace name="backend/paypal_unified/capture/authorize"}
// {block name="backend/paypal_unified/capture/authorize"}
Ext.define('Shopware.apps.PaypalUnified.view.capture.Authorize', {
    extend: 'Enlight.app.Window',
    title: '{s name=title}Capture Payment{/s}',
    alias: 'widget.paypal-unified-capture-authorize',

    maximizable: false,
    resizable: false,
    modal: true,
    height: 210,
    width: 400,
    layout: 'anchor',

    /**
     * @type { Ext.form.Panel }
     */
    contentContainer: null,

    /**
     * @type { Ext.toolbar.Toolbar }
     */
    toolbar: null,

    initComponent: function() {
        this.items = this.getItems();
        this.dockedItems = this.createToolbar();

        this.registerEvents();
        this.callParent(arguments);
    },

    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Will be fired if the user wants to authorize a payment.
             *
             * @param { Numeric } amount
             * @param { Boolean } isFinal
             */
            'authorizePayment'
        );
    },

    /**
     * @param { Number } maxAmount
     */
    showDialog: function (maxAmount) {
        this.show();
        this.down('#maxAmount').setValue(maxAmount);
        this.down('#currentAmount').setValue(maxAmount);
    },

    /**
     * @returns { Ext.form.Panel }
     */
    getItems: function () {
        var me = this;

        me.contentContainer = Ext.create('Ext.form.Panel', {
            fieldDefaults: {
                labelWidth: 180,
                anchor: '100%'
            },

            border: false,
            bodyPadding: 20,

            items: [{
                xtype: 'textfield',
                name: 'id',
                itemId: 'saleId',
                hidden: true
            }, {
                xtype: 'textfield',
                itemId: 'maxAmount',
                disabled: true,
                fieldLabel: '{s name=field/maxAmount}Maximum amount{/s}'
            }, {
                xtype: 'base-element-number',
                itemId: 'currentAmount',
                fieldLabel: '{s name=field/currentAmount}Amount{/s}',
                allowDecimals: true,
                minValue: 0.01
            }, {
                xtype: 'checkbox',
                itemId: 'finalCapture',
                inputValue: true,
                uncheckedValue: false,
                checked: true,
                fieldLabel: '{s name=field/isFinal}This is the final capture{/s}'
            }]
        });

        return me.contentContainer;
    },

    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            ui: 'shopware-ui',

            items: ['->', {
                xtype: 'base-element-button',
                text: '{s name=field/cancelButton}Cancel{/s}',
                region: 'right',
                cls: 'secondary',
                handler: Ext.bind(me.close, me)
            }, {
                xtype: 'base-element-button',
                text: '{s name=field/captureButton}Capture payment{/s}',
                region: 'right',
                cls: 'primary',
                handler: Ext.bind(me.captureButtonClick, me)
            }]
        });

        return me.toolbar;
    },

    captureButtonClick: function () {
        var amount = this.down('#currentAmount').value,
            isFinal = this.down('#finalCapture').checked;

        this.fireEvent('authorizePayment', amount, isFinal);
        this.close();
    }
});
// {/block}
