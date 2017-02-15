//{namespace name="backend/paypal_unified_settings/tabs/general"}
//{block name="backend/paypal_unified_settings/tabs/general"}
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
        labelWidth: '180px'
    },

    /**
     * @type { Ext.form.FieldSet }
     */
    restContainer: null,

    /**
     * @type { Ext.form.Panel }
     */
    behaviorContainer: null,

    initComponent : function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);

        //Manually set the background color of the toolbar.
        me.down('*[name=toolbarContainer]').setBodyStyle(me.style);
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * Will be fired when the user clicks on the register webhook button
             */
            'registerWebhook'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this;

        return [ me.createRestContainer(), me.createBehaviorContainer() ]
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createRestContainer: function () {
        var me = this;

        me.restContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/rest/title"}API Settings{/s}',

            items: [{
                xtype: 'base-element-boolean',
                name: 'sandbox',
                fieldLabel: '{s name="fieldset/rest/enableSandbox"}Client-Secret{/s}'
            }, {
                xtype: 'textfield',
                name: 'clientId',
                fieldLabel: '{s name="fieldset/rest/clientId"}Client-ID{/s}'
            }, {
                xtype: 'textfield',
                name: 'clientSecret',
                fieldLabel: '{s name="fieldset/rest/clientSecret"}Client-Secret{/s}'
            }, me.createToolbar()]
        });

        return me.restContainer;
    },

    /**
     * @returns { Ext.form.FieldSet }
     */
    createBehaviorContainer: function () {
        var me = this;

        me.behaviorContainer = Ext.create('Ext.form.FieldSet', {
            title: '{s name="fieldset/behavior/title"}API Settings{/s}',
            items: [{
                xtype: 'base-element-boolean',
                name: 'showSidebarLogo',
                fieldLabel: '{s name="fieldset/behavior/showSidebarLogo"}Show logo in sidebar{/s}',
            }, {
                xtype: 'textfield',
                name: 'brandName',
                fieldLabel: '{s name="fieldset/behavior/brandName"}Brand name{/s}'
            }, {
                xtype: 'base-element-media',
                name: 'logoImage',
                fieldLabel: '{s name="fieldset/behavior/logoImage"}Logo{/s}'
            }, {
                xtype: 'base-element-boolean',
                name: 'sendOrderNumber',
                fieldLabel: '{s name="fieldset/behavior/sendOrderNumber"}Send order number to PayPal{/s}',
                handler: Ext.bind(me.onSendOrderNumberChecked, me)
            }, {
                xtype: 'textfield',
                name: 'orderNumberPrefix',
                fieldLabel: '{s name="fieldset/behavior/orderNumberPrefix"}Order number prefix{/s}',
                disabled: true
            }]
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
                }
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

        me.down('*[name=orderNumberPrefix]').setDisabled(!checked);
    },

    onRegisterWebhookButtonClick: function() {
        var me = this;

        me.fireEvent('registerWebhook');
    }
});
//{/block}
