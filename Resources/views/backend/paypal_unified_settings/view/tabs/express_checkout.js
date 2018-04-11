// {namespace name="backend/paypal_unified_settings/tabs/express_checkout"}
// {block name="backend/paypal_unified_settings/tabs/express_checkout"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.ExpressCheckout', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-express-checkout',
    title: '{s name=title}PayPal Express Checkout integration{/s}',

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

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecActivate: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecIntentSelection: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecDetailActivate: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecCartActivate: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecLoginActivate: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecButtonStyleColor: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecButtonStyleShape: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecButtonStyleSize: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecSubmitCart: null,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this;

        me.ecIntentSelection = me.createPaymentIntentSelection();
        me.ecDetailActivate = me.createEcDetailActivate();
        me.ecCartActivate = me.createEcCartActivate();
        me.ecLoginActivate = me.createEcLoginActivate();
        me.ecButtonStyleColor = me.createEcButtonStyleColor();
        me.ecButtonStyleShape = me.createEcButtonStyleShape();
        me.ecButtonStyleSize = me.createEcButtonStyleSize();
        me.ecSubmitCart = me.createEcSubmitCart();

        return [
            {
                xtype: 'container',
                html: '{s name=description}PayPal Express Checkout - the PayPal button on the product detail page for maximum conversion<br>Find more infos about the PayPal Express Shortcut here: <a href="https://www.paypal.com/de/webapps/mpp/express-checkout" title="https://www.paypal.com/de/webapps/mpp/express-checkout" target="_blank">https://www.paypal.com/de/webapps/mpp/express-checkout</a>{/s}',
                margin: '0 0 20',
                style: {
                    'font-size': '16px',
                    'line-height': '30px'
                }
            },
            me.ecActivate,
            me.ecIntentSelection,
            me.ecDetailActivate,
            me.ecCartActivate,
            me.ecLoginActivate,
            me.ecButtonStyleColor,
            me.ecButtonStyleShape,
            me.ecButtonStyleSize,
            me.ecSubmitCart
        ];
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createPaymentIntentSelection: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'intent',
            fieldLabel: '{s name="intent/field" namespace="backend/paypal_unified_settings/tabs/payment_intent"}{/s}',
            helpText: '',

            store: {
                fields: [
                    { name: 'id', type: 'int' },
                    { name: 'text', type: 'string' }
                ],

                data: [
                    { id: 0, text: '{s name="intent/sale" namespace="backend/paypal_unified_settings/tabs/payment_intent"}Complete payment immediately (Sale){/s}' },
                    { id: 1, text: '{s name="intent/authCapture" namespace="backend/paypal_unified_settings/tabs/payment_intent"}Delayed payment collection (Auth-Capture){/s}' },
                    { id: 2, text: '{s name="intent/orderAuthCapture" namespace="backend/paypal_unified_settings/tabs/payment_intent"}Delayed payment collection (Order-Auth-Capture){/s}' }
                ]
            },

            valueField: 'id',
            value: 0
        });
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcDetailActivate: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'detailActive',
            fieldLabel: '{s name=field/ecDetailActivate}\'Pay now\' on detail page{/s}',
            boxLabel: '{s name=field/ecDetailActivate/help}If this option is active, the Express Checkout button will be shown on each product detail page.{/s}',
            inputValue: true,
            uncheckedValue: false
        });
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcCartActivate: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'cartActive',
            fieldLabel: '{s name=field/ecCartActivate}\'Pay now\' on cart{/s}',
            boxLabel: '{s name=field/ecCartActivate/help}If this option is active, the Express Checkout button will be shown on the cart.{/s}',
            inputValue: true,
            uncheckedValue: false
        });
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcLoginActivate: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'loginActive',
            fieldLabel: '{s name=field/ecLoginActivate}\'Pay now\' on login page{/s}',
            boxLabel: '{s name=field/ecLoginActivate/help}If this option is active, the Express Checkout button will be shown on the login and register page.{/s}',
            inputValue: true,
            uncheckedValue: false
        });
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcSubmitCart: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'submitCart',
            fieldLabel: '{s name=field/submitCart}Submit cart{/s}',
            boxLabel: '{s name=field/submitCart/help}If this option is active, the cart will be submitted to PayPal for Express orders{/s}',
            inputValue: true,
            uncheckedValue: false
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createEcButtonStyleColor: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'buttonStyleColor',
            fieldLabel: '{s name=field/ecButtonStyleColor}Button color{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleColor'),
            valueField: 'id'
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createEcButtonStyleShape: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'buttonStyleShape',
            fieldLabel: '{s name=field/ecButtonStyleShape}Button shape{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleShape'),
            valueField: 'id'
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createEcButtonStyleSize: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'buttonStyleSize',
            fieldLabel: '{s name=field/ecButtonStyleSize}Button size{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleSize'),
            valueField: 'id'
        });
    }
});
// {/block}
