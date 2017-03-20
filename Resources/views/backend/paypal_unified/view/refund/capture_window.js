//{namespace name="backend/paypal_unified/refund/capture_window"}
//{block name="backend/paypal_unified/refund/capture_window"}
Ext.define('Shopware.apps.PaypalUnified.view.refund.CaptureWindow', {
    extend: 'Enlight.app.Window',
    title: '{s name=title}New Refund{/s}',
    alias: 'widget.paypal-unified-refund-capture-window',

    maximizable: false,
    resizable: false,
    modal: true,
    height: 225,
    width: 400,
    layout: 'anchor',

    /**
     * @type { Ext.form.Panel }
     */
    contentContainer: null,

    initComponent: function () {
        var me = this;

        me.items = me.getItems();
        me.registerEvents();

        me.callParent(arguments);

        //For some reason the configuration in me.getItems() is being ignored...
        me.down('#executeButton').disable();
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * Will be fired if the user wants to refund a capture
             *
             * @param { Object } data
             */
            'refundCapture'
        );
    },

    /**
     * Since we have a custom store without any listener we have to
     * override the original close function, otherwise that would cause an ExtJs error and the window
     * would not be closed correctly.
     */
    close: function () {
        var me = this;
        me.destroy();
    },

    /**
     * @returns { Ext.form.Panel }
     */
    getItems: function () {
        var me = this;

        me.contentContainer = Ext.create('Ext.form.Panel', {
            border: false,
            bodyPadding: 10,
            fieldDefaults: { anchor: '100%' },
            items: [{
                xtype: 'textfield',
                name: 'id',
                itemId: 'id',
                hidden: true
            }, {
                xtype: 'combo',
                itemId: 'captureSelection',
                fieldLabel: '{s name=field/captureSelection}Select capture{/s}',
                valueField: 'id',
                displayField: 'description',
                listeners: {
                    select: Ext.bind(me.onSelectCapture, me)
                }
            }, {
                xtype: 'base-element-number',
                itemId: 'currentAmount',
                fieldLabel: '{s name=field/current}Amount{/s}',
                helpText: '{s name=field/current/help}Enter the amount you would like to refund.{/s}',
                allowDecimals: true,
                disabled: true,
                minValue: 0.01
            }, {
                xtype: 'textarea',
                itemId: 'note',
                fieldLabel: '{s name=field/description}Note{/s}',
                disabled: true,
                grow: false
            }, {
                xtype: 'base-element-button',
                text: '{s name=field/button}Execute{/s}',
                itemId: 'executeButton',
                region: 'right',
                anchor: '100%',
                margin: 10,
                cls: 'primary',
                disabled: true,
                handler: Ext.bind(me.onRefundButtonClick, me)
            }]
        });

        return me.contentContainer;
    },

    /**
     * @param { Array } captures
     */
    setCaptures: function (captures) {
        var me = this,
            store = { };

        store.fields = [ 'id', 'amount', 'create_time', 'description' ];
        store.data = captures;

        me.down('#captureSelection').store = store;
    },

    /**
     * @param { Ext.form.ComboBox } element
     * @param { Ext.data.Model } record
     */
    onSelectCapture: function (element, record) {
        var me = this,
            selectedRecord = record[0];

        if (!selectedRecord) {
            return;
        }

        me.down('#id').setValue(selectedRecord.get('id'));
        me.down('#currentAmount').enable();
        me.down('#note').enable();
        me.down('#executeButton').enable();
    },

    /**
     * Handler for the refund button event.
     * Barely validates the input and then displays a confirmation
     * message box. If the user clicks "yes" it will continue the refund process.
     */
    onRefundButtonClick: function () {
        var me = this,
            amount = me.down('#currentAmount').value,
            amountFormatted = Ext.util.Format.number(amount, '0,000.00 EUR');

        if (amount <= 0) {
            Ext.MessageBox.alert(
                '{s name=alert/title/amount}Invalid amount{/s}',
                '{s name=alert/message/amountZero}The amount you have entered is invalid. Please enter an amount which is <b>greater than 0€</b>{/s}'
            );

            return;
        }

        Ext.MessageBox.confirm(
            '{s name=confirm/title}Execute refund?{/s}',
            '{s name=confirm/message}Do you really want to refund this amount: {/s}<b>' + amountFormatted + '</b>?',
            Ext.bind(me.onConfirmRefund, me)
        );
    },

    /**
     * @param { String } response
     */
    onConfirmRefund: function(response) {
        var me = this,
            id = me.down('#id').value,
            note = me.down('#note').value,
            amount = me.down('#currentAmount').value;

        if (response === 'yes') {
            var params = {
                'id': id,
                'amount': amount,
                'note': note
            };

            me.fireEvent('refundCapture', params);

            me.destroy();
        }
    }
});
//{/block}