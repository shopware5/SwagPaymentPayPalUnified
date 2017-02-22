//{namespace name="backend/paypal_unified/sidebar/toolbar"}
//{block name="backend/paypal_unified/sidebar/sidebar/toolbar"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebar.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.paypal-unified-sidebar-order-actions',

    hidden: true,
    dock: 'bottom',
    anchor: '100%',
    padding: '10 0 5',
    ui: 'shopware-ui',

    fieldDefaults: {
        anchor: '100%',
        readOnly: true
    },

    /**
     * @type { String }
     */
    intent: null,

    /**
     * @type { Numeric }
     */
    amount: null,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return ['->', {
            xtype: 'button',
            cls: 'secondary',
            name: 'voidButton',
            itemId: 'voidButton',
            text: '{s name="button/cancelAuthorization"}Cancel authorization{/s}',
            handler: Ext.bind(me.voidButtonClick, me)
        }, {
            xtype: 'button',
            cls: 'primary',
            name: 'authorizeButton',
            itemId: 'authorizeButton',
            text: '{s name="button/authorize"}Authorize{/s}',
            handler: Ext.bind(me.authorizeButtonClick, me)
        }];
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * This event will be fired when the user wants to void the payment.
             */
            'voidPayment'
        );
    },

    /**
     * @param { String } intent
     * @param { Numeric } amount
     * @param { Boolean } allowVoid
     */
    updateToolbar: function(intent, amount, allowVoid) {
        var me = this,
            voidButton = me.down('#voidButton'),
            authorizeButton = me.down('#authorizeButton');

        if (amount == 0 || intent === 'sale') {
            me.hide();

            return;
        }

        me.show();
        authorizeButton.show();
        voidButton.setVisible(allowVoid);

        me.intent = intent;
        me.amount = amount;
    },

    authorizeButtonClick: function () {
        var me = this,
            captureDialog = Ext.create('Shopware.apps.PaypalUnified.view.capture.Authorize');

        captureDialog.showDialog(me.amount);
    },

    voidButtonClick: function () {
        var me = this;

        Ext.MessageBox.confirm(
            '{s name=confirm/title}Void Payment?{/s}',
            '{s name=confirm/message}Do you really want to void this payment?{/s}',
            function (response) {
                if (response === 'yes') {
                    me.intent === 'authorize' ? me.fireEvent('voidAuthorization') : me.fireEvent('voidOrder');
                }
            }
        );
    }
});
//{/block}