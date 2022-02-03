// {block name="backend/payment/controller/payment"}
// {$smarty.block.parent}
// {namespace name="backend/payment/controller/payment"}
Ext.define('Shopware.apps.Payment.controller.PaymentPaypalUnified', {
    override: 'Shopware.apps.Payment.controller.Payment',

    snippets: {
        onboardingMessageText: '{s name="onboardingMessageText"}Please be aware, that your PayPal-account needs to be eligible for receiving payments with "Pay Upon Invoice", for this payment method to be available to your customers. You may authorize your account in the PayPal settings module.{/s}'
    },

    onboardingMessage: null,
    payUponInvoicePaymentMethodName: 'SwagPaymentPayPalUnifiedPayUponInvoice',

    /**
     * @param { Ext.view.View } view
     * @param { Ext.data.Record } record
     */
    onItemClick: function (view, record) {
        var me = this,
            win = view.up('window'),
            tabPanel = win.tabPanel,
            form = win.generalForm,
            treeToolBar = win.down('toolbar[name=treeToolBar]'),
            gridToolBar = win.down('toolbar[name=gridToolBar]'),
            btnSave = gridToolBar.down('button[name=save]'),
            btnDelete = treeToolBar.down('button[name=delete]'),
            surchargeGrid = win.down('payment-main-surcharge');

        if (record.get('name') === this.payUponInvoicePaymentMethodName) {
            form.insert(0, this._createOnboardingMessage())
        } else if (this.onboardingMessage !== null) {
            form.remove(this.onboardingMessage);

            this.onboardingMessage = null;
        }

        me.callParent(arguments);
    },

    _createOnboardingMessage: function () {
        this.onboardingMessage = Shopware.Notification.createBlockMessage(
            this.snippets.onboardingMessageText,
            'alert'
        );

        return this.onboardingMessage;
    },
});
// {/block}
