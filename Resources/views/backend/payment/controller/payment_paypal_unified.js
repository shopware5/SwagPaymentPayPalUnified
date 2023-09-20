// {block name="backend/payment/controller/payment"}
// {$smarty.block.parent}
// {namespace name="backend/payment/controller/payment"}
Ext.define('Shopware.apps.Payment.controller.PaymentPaypalUnified', {
    override: 'Shopware.apps.Payment.controller.Payment',

    snippets: {
        onboardingMessageText: '{s name="onboardingMessageText"}Please be aware, that your PayPal-account needs to be eligible for receiving payments with "Pay Upon Invoice", for this payment method to be available to your customers. You may authorize your account in the PayPal settings module.{/s}',
        myBankDisclaimerText: '{s name="myBankDisclaimerText"}Merchants enabling MyBank after February 2023 will need manual approval by PayPal. Reach out to merchant support for further information on this.{/s}',
    },

    onboardingMessage: null,
    payUponInvoicePaymentMethodName: 'SwagPaymentPayPalUnifiedPayUponInvoice',
    myBankPaymentMethodName: 'SwagPaymentPayPalUnifiedMyBank',

    /**
     * @param { Ext.view.View } view
     * @param { Ext.data.Record } record
     */
    onItemClick: function (view, record) {
        var win = view.up('window'),
            form = win.generalForm,
            treeToolBar = win.down('toolbar[name=treeToolBar]'),
            gridToolBar = win.down('toolbar[name=gridToolBar]'),
            btnSave = gridToolBar.down('button[name=save]'),
            btnDelete = treeToolBar.down('button[name=delete]'),
            surchargeGrid = win.down('payment-main-surcharge');

        if (record.get('name') === this.payUponInvoicePaymentMethodName) {
            form.insert(0, this._createOnboardingMessage());
        } else if (this.onboardingMessage !== null) {
            form.remove(this.onboardingMessage);

            this.onboardingMessage = null;
        }

        if (record.get('name') === this.myBankPaymentMethodName) {
            form.insert(0, this._createMyBankDisclaimer());
        } else if (this.myBankDisclaimer !== null) {
            form.remove(this.myBankDisclaimer);

            this.myBankDisclaimer = null;
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

    _createMyBankDisclaimer: function () {
        this.myBankDisclaimer = Shopware.Notification.createBlockMessage(
            this.snippets.myBankDisclaimerText,
            'alert'
        );

        return this.myBankDisclaimer;
    },
});
// {/block}
