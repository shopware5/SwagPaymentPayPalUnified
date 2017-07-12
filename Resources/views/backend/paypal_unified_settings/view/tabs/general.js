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
    behaviorContainer: null,

    /**
     * @type { Ext.form.FieldSet }
     */
    activationContainer: null,

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
            'onChangeShopActivation'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [
            me.createActivationContainer(),
            me.createRestContainer(),
            me.createBehaviorContainer()
        ];
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
                    boxLabel: '{s name="fieldset/activation/activate/help"}Enable this option to activate PayPal Products for this shop.{/s}',
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
                    xtype: 'checkbox',
                    name: 'sandbox',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/rest/enableSandbox"}Enable sandbox{/s}',
                    boxLabel: '{s name="fieldset/rest/enableSandbox/help"}Enable this option to test the integration.{/s}'
                }, {
                    xtype: 'textfield',
                    name: 'clientId',
                    fieldLabel: '{s name="fieldset/rest/clientId"}Client-ID{/s}',
                    helpText: '{s name="fieldset/rest/clientId/help"}The REST-API Client-ID that is being used to authenticate this plugin to the PayPal API.{/s}',
                    allowBlank: false
                }, {
                    xtype: 'textfield',
                    name: 'clientSecret',
                    fieldLabel: '{s name="fieldset/rest/clientSecret"}Client-Secret{/s}',
                    helpText: '{s name="fieldset/rest/clientSecret/help"}The REST-API Client-Secret that is being used to authenticate this plugin to the PayPal API.{/s}',
                    allowBlank: false
                },
                me.toolbarContainer
            ]
        });

        return me.restContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createBehaviorContainer: function () {
        var me = this;

        me.orderNumberPrefix = Ext.create('Ext.form.field.Text', {
            name: 'orderNumberPrefix',
            fieldLabel: '{s name="fieldset/behavior/orderNumberPrefix"}Order number prefix{/s}',
            helpText: '{s name="fieldset/behavior/orderNumberPrefix/help"}The text you enter here will be placed before the actual order number (e.g MyShop_%orderNumber%). This helps to identify the shop in which this order has been taken in.{/s}',
            disabled: true
        });

        me.behaviorContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/behavior/title"}Behavior{/s}',
            items: [
                {
                    xtype: 'checkbox',
                    name: 'showSidebarLogo',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behavior/showSidebarLogo"}Show logo in sidebar{/s}',
                    boxLabel: '{s name="fieldset/behavior/showSidebarLogo/help"}Enable this option to show the PayPal logo in the storefront sidebar.{/s}'
                }, {
                    xtype: 'textfield',
                    name: 'brandName',
                    fieldLabel: '{s name="fieldset/behavior/brandName"}Brand name on the PayPal page{/s}',
                    helpText: '{s name="fieldset/behavior/brandName/help"}This text will be displayed as the brand name on the PayPal payment page.{/s}'
                }, {
                    xtype: 'base-element-media',
                    name: 'logoImage',
                    fieldLabel: '{s name="fieldset/behavior/logoImage"}Logo on the PayPal pag{/s}',
                    helpText: '{s name="fieldset/behavior/logoImage/help"}The image you have selected here will be displayed as the brand logo on the PayPal payment page.{/s}',
                    allowBlank: false // logoImage is required for experience profile
                }, {
                    xtype: 'checkbox',
                    name: 'sendOrderNumber',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behavior/sendOrderNumber"}Send order number to PayPal{/s}',
                    boxLabel: '{s name="fieldset/behavior/sendOrderNumber/help"}Enable this option to send the order number to PayPal after an order has been completed.{/s}',
                    handler: Ext.bind(me.onSendOrderNumberChecked, me)
                },
                me.orderNumberPrefix,
                {
                    xtype: 'checkbox',
                    name: 'useInContext',
                    inputValue: true,
                    uncheckedValue: false,
                    fieldLabel: '{s name="fieldset/behaviour/useInContext"}Use in-context mode{/s}',
                    helpText: '{s name="fieldset/behaviour/useInContext/help"}Enable this option to use the PayPal in-context solution. Instead of redirecting to the PayPal login page, an overlay will be shown and the customer does not need to leave the shop.{/s}'
                }
            ]
        });

        return me.behaviorContainer;
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
