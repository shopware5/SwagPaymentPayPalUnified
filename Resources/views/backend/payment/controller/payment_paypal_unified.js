// {block name="backend/payment/controller/payment"}
// {$smarty.block.parent}
// {namespace name="backend/payment/controller/payment"}
Ext.define('Shopware.apps.Payment.controller.PaymentPaypalUnified', {
    override: 'Shopware.apps.Payment.controller.Payment',

    snippets: {
        onboardingMessageText: '{s name="onboardingMessageText"}Please be aware, that your PayPal-account needs to be eligible for receiving payments with "Pay Upon Invoice", for this payment method to be available to your customers. You may authorize your account in the PayPal settings module.{/s}',
        myBankDisclaimerText: '{s name="myBankDisclaimerText"}Merchants enabling MyBank after February 2023 will need manual approval by PayPal. Reach out to merchant support for further information on this.{/s}',
        sofortDisclaimerText: '{s name="sofortDisclaimerText"}Enabling SOFORT after October 2023 is no longer allowed for new merchants. This is in preparation of the full product sunset by September 2024 — as announced by Klarna.{/s}',
    },

    disclaimerMessage: null,
    messages: [
        {
            paymentMethodName: 'SwagPaymentPayPalUnifiedPayUponInvoice',
            snippet: 'onboardingMessageText'
        },
        {
            paymentMethodName: 'SwagPaymentPayPalUnifiedMyBank',
            snippet: 'myBankDisclaimerText'
        },
        {
            paymentMethodName: 'SwagPaymentPayPalUnifiedSofort',
            snippet: 'sofortDisclaimerText'
        },
    ],

    payUponInvoicePaymentMethodName: 'SwagPaymentPayPalUnifiedPayUponInvoice',
    myBankPaymentMethodName: 'SwagPaymentPayPalUnifiedMyBank',
    sofortPaymentMethodName: 'SwagPaymentPayPalUnifiedSofort',

    /**
     * @param { Ext.view.View } view
     * @param { Ext.data.Record } record
     */
    onItemClick: function (view, record) {
        var win = view.up('window');

        this.form = win.generalForm;
        this.currentRecord = record;

        this._handleDisclaimer();

        this.callParent(arguments);
    },

    _handleDisclaimer: function () {
        this._removeDisclaimer();

        var me = this;
        this.messages.forEach(function (message) {
            if (me.currentRecord.get('name') === message.paymentMethodName) {
                me._addDisclaimer(message.snippet);
            }
        });
    },

    _removeDisclaimer: function () {
        if (this.disclaimerMessage !== null) {
            this.form.remove(this.disclaimerMessage);
            this.disclaimerMessage = null;
        }
    },

    _addDisclaimer: function (snippetKey) {
        this.disclaimerMessage = Shopware.Notification.createBlockMessage(
            this.snippets[snippetKey],
            'alert'
        );

        this.form.insert(0, this.disclaimerMessage);
    },
});
// {/block}
