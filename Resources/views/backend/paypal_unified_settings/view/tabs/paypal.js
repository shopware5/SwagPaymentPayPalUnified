// {namespace name="backend/paypal_unified_settings/tabs/paypal"}
// {block name="backend/paypal_unified_settings/tabs/paypal"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.Paypal', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-paypal',
    title: '{s name=title}PayPal Integration{/s}',

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

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        return [
            {
                xtype: 'combobox',
                name: 'paypalPaymentIntent',
                fieldLabel: '{s name=field/paymentIntent}Payment acquisition{/s}',
                store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.PaymentIntent'),
                valueField: 'id',
                value: 0
            },
            {
                xtype: 'container',
                html: "{s name=field/paymentIntent/support}* Please be aware, that this setting may have no impact on 'PayPayl Plus' and 'PayPal Installments'. <br/> If active, 'Paypal Plus' always uses 'Complete payment immediately (Sale)'. <br/> If active 'PayPal Installments' uses either 'Complete payment immediately (Sale)' or 'Delayed payment collection (Order-Auth-Capture)'.{/s}",
                margin: '10 0 0 0',
                style: {
                    color: '#475c6a'
                }
            }
        ];
    }
});
// {/block}
