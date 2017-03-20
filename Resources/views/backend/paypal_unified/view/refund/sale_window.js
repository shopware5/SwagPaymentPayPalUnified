//{namespace name="backend/paypal_unified/refund/sale_window"}
//{block name="backend/paypal_unified/refund/sale_window"}
Ext.define('Shopware.apps.PaypalUnified.view.refund.SaleWindow', {
    extend: 'Enlight.app.Window',
    title: '{s name=title}New Refund{/s}',
    alias: 'widget.paypal-unified-refund-sale-window',

    maximizable: false,
    resizable: false,
    modal: true,
    height: 220,
    width: 400,
    layout: 'anchor',

    /**
     * @type { Ext.form.Panel }
     */
    contentContainer: null,

    initComponent: function() {
        var me = this;

        me.items = me.getItems();
        me.registerEvents();

        me.callParent(arguments);
    },

    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Will be fired if the user wants to refund a sale.
             *
             * @param { Object } data
             */
            'refundSale'
        );
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
                xtype: 'textfield',
                itemId: 'maxAmount',
                disabled: true,
                fieldLabel: '{s name=field/max}Maximum amount{/s}',
                helpText: '{s name=field/max/help}The maximum amount that can be refunded.{/s}',
            }, {
                xtype: 'base-element-number',
                itemId: 'currentAmount',
                fieldLabel: '{s name=field/current}Amount{/s}',
                helpText: '{s name=field/current/help}Enter the amount you would like to refund.{/s}',
                allowDecimals: true,
                minValue: 0.01
            }, {
                xtype: 'checkbox',
                itemId: 'refundCompletely',
                fieldLabel: '{s name=field/refundCompletely}Refund completely{/s}',
                helpText: '{s name=field/refundCompletely/help}Enable this option to refund the complete sale. (This does not work for partially refunded sales){/s}',
                handler: Ext.bind(me.onCheckRefundCompletely, me)
            }, {
                xtype: 'textfield',
                itemId: 'invoiceNumber',
                fieldLabel: '{s name=field/bookingNumber}Booking number{/s}',
                helpText: '{s name=field/bookingNumber/help}You can enter a booking number if would like to identify this refund later.{/s}',
                emptyText: '{s name=field/bookingNumber/empty}No booking number{/s}',
            }, {
                xtype: 'base-element-button',
                text: '{s name=field/button}Execute{/s}',
                region: 'right',
                anchor: '100%',
                margin: 10,
                cls: 'primary',
                handler: Ext.bind(me.onRefundButtonClick, me)
            }]
        });

        return me.contentContainer;
    },

    /**
     * Handler for the refund button event.
     * Barely validates the input and then displays a confirmation
     * message box. If the user clicks "yes" it will continue the refund process.
     */
    onRefundButtonClick: function () {
        var me = this,
            amount = me.down('#currentAmount').value,
            amountFormatted = Ext.util.Format.number(amount, '0,000.00 EUR'),
            maxAmount = me.down('#maxAmount').value,
            maxAmountFormatted = Ext.util.Format.number(maxAmount, '0,000.00 EUR');

        if (amount <= 0) {
            Ext.MessageBox.alert(
                '{s name=alert/title/amount}Invalid amount{/s}',
                '{s name=alert/message/amountZero}The amount you have entered is invalid. Please enter an amount which is <b>greater than 0€</b>{/s}'
            );

            return;
        }

        if (amount > maxAmount) {
            Ext.MessageBox.alert(
                '{s name=alert/title/amount}Invalid amount{/s}',
                '{s name=alert/message/amountMax}The amount you have entered is invalid. You can not refund an amount which is greater than {/s}' + maxAmountFormatted
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
            invoiceNumber = me.down('#invoiceNumber').value,
            amount = me.down('#currentAmount').value,
            refundCompletely = me.down('#refundCompletely').checked;

        if (response === 'yes') {
            var params = {
                'id': id,
                'amount': amount,
                'refundCompletely': refundCompletely,
                'invoiceNumber': invoiceNumber
            };

            me.fireEvent('refundSale', params);

            me.destroy();
        }
    },

    /**
     * Handler for the refundCompletely checkbox.
     *
     * @param { Ext.form.field.Checkbox } element
     * @param { Boolean } checked
     */
    onCheckRefundCompletely: function (element, checked) {
        var me = this,
            currentAmountElement = me.down('#currentAmount'),
            maxAmountElement = me.down('#maxAmount'),
            value = checked ? maxAmountElement.value : null;

        currentAmountElement.setReadOnly(checked);
        currentAmountElement.setValue(value);
    }
});
//{/block}