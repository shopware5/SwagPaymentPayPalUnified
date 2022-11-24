// {namespace name="backend/paypal_unified_settings/tabs/installments"}
// {block name="backend/paypal_unified_settings/tabs/installment"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.Installments', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-installments',
    title: '{s name="new/title"}PayLater{/s}',

    anchor: '100%',
    bodyPadding: 10,
    border: false,

    style: {
        background: '#EBEDEF'
    },

    fieldDefaults: {
        anchor: '100%',
        labelWidth: 180
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.registerEvents();

        me.callParent(arguments);
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * This event will be triggered when the user clicks the button to test the installments availability.
             */
            'testInstallmentsAvailability'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this;

        return [
            me.createNotice(),
            {
                xtype: 'checkbox',
                name: 'advertiseInstallments',
                inputValue: true,
                uncheckedValue: false,
                fieldLabel: '{s name="field/advertiseInstallments"}Installments banner{/s}',
                boxLabel: '{s name="field/advertiseInstallments/boxLabel"}Enable to advertise installments via PayPal. Works only with a Live Client-ID.{/s}',
            },
            {
                xtype: 'checkbox',
                name: 'showPayLaterPaypal',
                inputValue: true,
                uncheckedValue: false,
                fieldLabel: '{s name="field/showPayLaterAtInContextButton/lable"}Show Pay later under In-Context Button{/s}',
                boxLabel: '{s name="field/showPayLaterAtInContextButton/boxLable"}Enable to show the "Pay Later" button under the "PayPal" In-Context button.{/s}',
                helpText: '{s name="field/showPayLaterAt/helpText "}This setting is not dependent on the activation of the payment method PayPal pay later.{/s}'
            },
            {
                xtype: 'checkbox',
                name: 'showPayLaterExpress',
                inputValue: true,
                uncheckedValue: false,
                fieldLabel: '{s name="field/showPayLaterAtExpressButton/lable"}Show Pay Later under Express Button{/s}',
                boxLabel: '{s name="field/showPayLaterAtExpressButton/boxLable"}Activate to show the "Pay Later" button under the "PayPal Express" button.{/s}',
                helpText: '{s name="field/showPayLaterAt/helpText "}This setting is not dependent on the activation of the payment method PayPal pay later.{/s}'
            },
        ];
    },

    /**
     * @returns { Ext.form.Container }
     */
    createNotice: function () {
        var infoNotice = Shopware.Notification.createBlockMessage('{s name="new/description"}Offer PayPal installments with 0% effective annual interest rate to your customers. Find out <a href="https://www.paypal.com/en/enterprise/pay-later" title="https://www.paypal.com/en/enterprise/pay-later" target="_blank">more here</a>.{/s}', 'info');

        // There is no style defined for the type "info" in the shopware backend stylesheet, therefore we have to apply it manually
        infoNotice.style = {
            'color': 'white',
            'font-size': '14px',
            'background-color': '#4AA3DF',
            'text-shadow': '0 0 5px rgba(0, 0, 0, 0.3)'
        };

        return infoNotice;
    }
});
// {/block}
