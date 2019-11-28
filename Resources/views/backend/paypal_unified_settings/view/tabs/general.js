// {namespace name="backend/paypal_unified_settings/tabs/general"}
// {block name="backend/paypal_unified_settings/tabs/general"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.General', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-general',
    title: '{s name="title"}General settings{/s}',

    anchor: '100%',
    border: false,
    bodyPadding: 10,

    style: {
        background: '#EBEDEF'
    },

    fieldDefaults: {
        anchor: '100%',
        labelWidth: 180
    },

    /**
     * @type { Ext.form.FieldSet }
     */
    restContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    behaviourContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    activationContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    errorHandlingContainer: null,

    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);

        // Manually set the background color of the toolbar.
        me.toolbarContainer.setBodyStyle(me.style);
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * Will be fired when the user clicks on the register webhook button
             */
            'registerWebhook',

            /**
             * Will be fired when the user clicks on the Test API settings button
             */
            'validateAPI',

            /**
             * Will be fired when the user enables/disables the activation for the selected shop
             *
             * @param { Boolean }
             */
            'onChangeShopActivation',

            /**
             * Will be fired when the user changes the merchant location
             *
             * @param { String }
             */
            'onChangeMerchantLocation'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [
            me.createNotice(),
            me.createActivationContainer(),
            me.createRestContainer(),
            me.createBehaviourContainer(),
            me.createErrorHandlingContainer()
        ];
    },

    /**
     * @returns { Ext.form.Container }
     */
    createNotice: function () {
        var infoNotice = Shopware.Notification.createBlockMessage('{s name=description}PayPal - the PayPal button in the checkout! Register for your PayPal business account here: <a href="https://www.paypal.com/de/webapps/mpp/express-checkout" title="https://www.paypal.com/de/webapps/mpp/express-checkout" target="_blank">https://www.paypal.com/de/webapps/mpp/express-checkout</a>{/s}', 'info');

        // There is no style defined for the type "info" in the shopware backend stylesheet, therefore we have to apply it manually
        infoNotice.style = {
            'color': 'white',
            'font-size': '14px',
            'background-color': '#4AA3DF',
            'text-shadow': '0 0 5px rgba(0, 0, 0, 0.3)'
        };

        return infoNotice;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createActivationContainer: function () {
        var me = this;

        me.activationContainer = Ext.create('Ext.form.FieldSet', {
            items: [
                {
                    xtype: 'checkbox',
                    name: 'active',
                    fieldLabel: '{s name="fieldset/activation/activate"}Enable for this shop{/s}',
                    boxLabel: '{s name="fieldset/activation/activate/help"}Enable this option to activate PayPal for this shop.{/s}',
                    inputValue: true,
                    uncheckedValue: false,
                    handler: function(element, checked) {
                        me.fireEvent('onChangeShopActivation', checked);
                    }
                }
            ]
        });

        return me.activationContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createRestContainer: function() {
        var me = this;

        me.toolbarContainer = me.createToolbar();

        me.restContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/rest/title"}API Settings{/s}',

            items: [
                {
                    xtype: 'textfield',
                    name: 'clientId',
                    fieldLabel: '{s name="fieldset/rest/clientId"}Client-ID{/s}',
                    helpText: '{s name="fieldset/rest/clientId/help"}The REST-API Client-ID that is being used to authenticate this plugin to the PayPal API.{/s}',
                    allowBlank: false
                },
                {
                    xtype: 'textfield',
                    name: 'clientSecret',
                    fieldLabel: '{s name="fieldset/rest/clientSecret"}Client-Secret{/s}',
                    helpText: '{s name="fieldset/rest/clientSecret/help"}The REST-API Client-Secret that is being used to authenticate this plugin to the PayPal API.{/s}',
                    allowBlank: false
                },
                {
                    xtype: 'checkbox',
                    name: 'sandbox',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/rest/enableSandbox"}Enable sandbox{/s}',
                    boxLabel: '{s name="fieldset/rest/enableSandbox/help"}Enable this option to test the integration.{/s}'
                },
                me.toolbarContainer
            ]
        });

        return me.restContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createBehaviourContainer: function () {
        var me = this;

        me.orderNumberPrefix = Ext.create('Ext.form.field.Text', {
            name: 'orderNumberPrefix',
            fieldLabel: '{s name="fieldset/behaviour/orderNumberPrefix"}Order number prefix{/s}',
            helpText: '{s name="fieldset/behaviour/orderNumberPrefix/help"}The text you enter here will be placed before the actual order number (e.g MyShop_%orderNumber%). This helps to identify the shop in which this order has been taken in.{/s}',
            disabled: true
        });

        me.smartPaymentButtonsCheckbox = Ext.create('Ext.form.field.Checkbox', {
            name: 'useSmartPaymentButtons',
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: '{s name="fieldset/behaviour/useSmartPaymentButtons"}Use Smart Payment Buttons{/s}',
            helpText: '{s name="fieldset/behaviour/useSmartPaymentButtons/helpText"}Enable this option to use the PayPal Smart Payment Buttons. Note that the Smart Payment Buttons are not available if your merchant location is Germany. The Smart Payment Buttons always use the in-context mode.{/s}'
        });

        me.behaviourContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/behaviour/title"}Behaviour{/s}',
            items: [
                {
                    xtype: 'combobox',
                    name: 'merchantLocation',
                    fieldLabel: '{s name="fieldset/behaviour/merchantLocation"}Merchant location{/s}',
                    helpText: '{s name="fieldset/behaviour/merchantLocation/help"}Choose your merchant location. Depending on this, different features are available to you.{/s}',
                    store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.MerchantLocation'),
                    valueField: 'type',
                    value: 'germany',
                    listeners: {
                        'select': function(checkbox) {
                            me.fireEvent('onChangeMerchantLocation', checkbox);
                        }
                    }
                },
                {
                    xtype: 'textfield',
                    name: 'brandName',
                    fieldLabel: '{s name="fieldset/behaviour/brandName"}Brand name on the PayPal page{/s}',
                    helpText: '{s name="fieldset/behaviour/brandName/help"}This text will be displayed as the brand name on the PayPal payment page.{/s}',
                    maxLength: 127
                },
                {
                    xtype: 'checkbox',
                    name: 'useInContext',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/useInContext"}Use in-context mode{/s}',
                    helpText: '{s name="fieldset/behaviour/useInContext/help"}Enable to use the PayPal in-context solution. Instead of redirecting to the PayPal login page, an overlay will be shown and the customer does not need to leave the shop. This option has no effect on the Smart Payment Buttons, as they always use the in-Context mode.{/s}'
                },
                {
                    xtype: 'checkbox',
                    name: 'submitCart',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/submitCart"}Submit cart{/s}',
                    helpText: '{s name="fieldset/behaviour/submitCart/help"}If this option is active, the cart will be submitted to PayPal.{/s}'
                },
                {
                    xtype: 'combobox',
                    name: 'landingPageType',
                    helpText: '{s name=fieldset/landingPage/help}<u>Login</u><br>The PayPal site displays a login screen as landingpage.<br><br><u>Registration</u><br>The PayPal site displays a registration form as landingpage.{/s}',
                    fieldLabel: '{s name=fieldset/landingPage/title}PayPal landingpage{/s}',
                    store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.LandingPageType'),
                    valueField: 'type',
                    value: 'Login'
                },
                {
                    xtype: 'checkbox',
                    name: 'showSidebarLogo',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/showSidebarLogo"}Show logo in sidebar{/s}',
                    boxLabel: '{s name="fieldset/behaviour/showSidebarLogo/help"}Enable this option to show the PayPal logo in the storefront sidebar.{/s}'
                },
                {
                    xtype: 'checkbox',
                    name: 'sendOrderNumber',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/sendOrderNumber"}Send order number to PayPal{/s}',
                    boxLabel: '{s name="fieldset/behaviour/sendOrderNumber/help"}Enable this option to send the order number to PayPal after an order has been completed.{/s}',
                    handler: Ext.bind(me.onSendOrderNumberChecked, me)
                },
                me.orderNumberPrefix,
                {
                    xtype: 'checkbox',
                    name: 'advertiseReturns',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/advertiseReturns"}Free returns{/s}',
                    boxLabel: '{s name="fieldset/behaviour/advertiseReturns/boxLabel"}Enable to advertise free returns via PayPal. More information <a href="https://www.paypal.com/de/webapps/mpp/returns-on-paypal" title="PayPal returns" target="_blank">here</a>{/s}',
                    helpText: '{s name="fieldset/behaviour/advertiseReturns/helpText"}If you already offer free returns, the use of the program is excluded.{/s}'
                },
                {
                    xtype: 'checkbox',
                    name: 'advertiseInstallments',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/advertiseInstallments"}Installments banner{/s}',
                    boxLabel: '{s name="fieldset/behaviour/advertiseInstallments/boxLabel"}Enable to advertise installments via PayPal.{/s}',
                },
                me.smartPaymentButtonsCheckbox
            ]
        });

        return me.behaviourContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createErrorHandlingContainer: function() {
        var me = this;

        me.errorHandlingContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/errorHandling/title"}Error handling{/s}',
            disabled: true,

            items: [{
                xtype: 'checkbox',
                name: 'displayErrors',
                helpText: '{s name=fieldset/errorHandling/displayErrors/help}If enabled, the communication error message will be displayed in the store front{/s}',
                fieldLabel: '{s name=fieldset/errorHandling/displayErrors}Display errors{/s}',
                inputValue: true,
                uncheckedValue: false
            }, {
                xtype: 'combobox',
                name: 'logLevel',
                helpText: '{s name=fieldset/errorHandling/logLevel/help}<u>Normal</u><br>Only errors will be logged to file.<br><br><u>Extended</u>Normal, Warning and Error messages will be logged to file. This is useful for debug environments.{/s}',
                fieldLabel: '{s name=fieldset/errorHandling/logLevel}Logging{/s}',
                store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.LogLevel'),
                valueField: 'id',
                value: 0
            }]
        });

        return me.errorHandlingContainer;
    },

    /**
     * @returns { Ext.form.Panel }
     */
    createToolbar: function () {
        var me = this;

        return Ext.create('Ext.form.Panel', {
            dock: 'bottom',
            border: false,
            bodyPadding: 5,
            name: 'toolbarContainer',

            items: [{
                xtype: 'button',
                cls: 'primary',
                text: '{s name="fieldset/rest/testButton"}Test API settings{/s}',
                style: {
                    float: 'right'
                },
                handler: Ext.bind(me.onValidateAPIButtonClick, me)
            }, {
                xtype: 'button',
                cls: 'secondary',
                text: '{s name="fieldset/rest/webhookButton"}Register Webhook{/s}',
                style: {
                    float: 'right'
                },
                handler: Ext.bind(me.onRegisterWebhookButtonClick, me)
            }]
        });
    },

    /**
     * @param { Shopware.apps.Base.view.element.Boolean } element
     * @param { Boolean } checked
     */
    onSendOrderNumberChecked: function (element, checked) {
        var me = this;

        me.orderNumberPrefix.setDisabled(!checked);
    },

    onValidateAPIButtonClick: function () {
        var me = this;

        me.fireEvent('validateAPI');
    },

    onRegisterWebhookButtonClick: function () {
        var me = this;

        me.fireEvent('registerWebhook');
    }
});
// {/block}
