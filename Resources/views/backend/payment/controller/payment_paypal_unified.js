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
        if (this.alreadyAdded === true) {
            this.callParent(arguments);
            return;
        }

        var win = view.up('window'),
            form = win.generalForm,
            treeToolBar = win.down('toolbar[name=treeToolBar]'),
            gridToolBar = win.down('toolbar[name=gridToolBar]'),
            btnSave = gridToolBar.down('button[name=save]'),
            btnDelete = treeToolBar.down('button[name=delete]'),
            surchargeGrid = win.down('payment-main-surcharge');

        if (record.get('name') === this.payUponInvoicePaymentMethodName) {
            form.insert(0, this._createOnboardingMessage());
            this.alreadyAdded = true;
        } else if (this.onboardingMessage !== null) {
            form.remove(this.onboardingMessage);

            this.onboardingMessage = null;
        }

        this.callParent(arguments);
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
