// {namespace name="backend/paypal_unified_settings/main"}
// {block name="backend/paypal_unified_settings/controller/main"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.controller.Main', {
    extend: 'Enlight.app.Controller',

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.Window }
     */
    window: null,

    /**
     * @type { Boolean }
     */
    settingsSaved: false,

    /**
     * @type { String }
     */
    detailUrl: '{url controller=PaypalUnifiedSettings action=detail}',

    /**
     * @type { String }
     */
    saveUrl: '{url controller=PaypalUnifiedSettings action=update}',

    /**
     * @type { String }
     */
    registerWebhookUrl: '{url controller=PaypalUnifiedSettings action=registerWebhook}',

    /**
     * @type { String }
     */
    validateAPIUrl: '{url controller=PaypalUnifiedSettings action=validateAPI}',

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.model.Settings }
     */
    record: null,

    refs: [
        { ref: 'generalTab', selector: 'paypal-unified-settings-tabs-general' },
        { ref: 'paypalTab', selector: 'paypal-unified-settings-tabs-paypal' },
        { ref: 'plusTab', selector: 'paypal-unified-settings-tabs-paypal-plus' },
        { ref: 'installmentsTab', selector: 'paypal-unified-settings-tabs-installments' },
        { ref: 'ecTab', selector: 'paypal-unified-settings-tabs-express-checkout' }
    ],

    init: function() {
        var me = this;

        me.createMainWindow();
        me.createComponentControl();

        me.callParent(arguments);
    },

    createComponentControl: function() {
        var me = this;

        me.control({
            'paypal-unified-settings-top-toolbar': {
                'changeShop': me.onChangeShop
            },
            'paypal-unified-settings-toolbar': {
                'saveSettings': me.onSaveSettings
            },
            'paypal-unified-settings-tabs-general': {
                'registerWebhook': me.onRegisterWebhook,
                'validateAPI': me.onValidateAPISettings,
                'onChangeShopActivation': me.applyActivationState
            }
        });
    },

    createMainWindow: function() {
        var me = this;
        me.window = me.getView('Window').create().show();
    },

    /**
     * @param { Number } shopId
     */
    loadDetails: function(shopId) {
        var me = this;
        Ext.Ajax.request({
            url: me.detailUrl,
            params: {
                shopId: shopId
            },
            callback: Ext.bind(me.onDetailAjaxCallback, me)
        });
    },

    /**
     * @param { Shopware.data.Model } record
     */
    onChangeShop: function(record) {
        var me = this,
            shopId = record.get('id');

        me.loadDetails(shopId);
    },

    onSaveSettings: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues(),
            paypalSettings = me.getPaypalTab().getForm().getValues(),
            plusSettings = me.getPlusTab().getForm().getValues(),
            installmentsSettings = me.getInstallmentsTab().getForm().getValues(),
            ecSettings = me.getEcTab().getForm().getValues();

        if (!me.getGeneralTab().getForm().isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/formValidationError}Please fill out all fields marked in red.{/s}', me.window.title);
            return;
        }

        me.record.set(generalSettings);
        me.record.set(paypalSettings);
        me.record.set(plusSettings);
        me.record.set(installmentsSettings);
        me.record.set(ecSettings);

        me.record.save();

        Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/saveSettings}The settings have been saved!{/s}', me.window.title);
    },

    onRegisterWebhook: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name="loading/registeringWebhook"}Registering webhook...{/s}');

        Ext.Ajax.request({
            url: me.registerWebhookUrl,
            params: {
                shopId: me.record.get('shopId'),
                clientId: generalSettings['clientId'],
                clientSecret: generalSettings['clientSecret'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onRegisterWebhookAjaxCallback, me)
        });
    },

    onValidateAPISettings: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name=loading/validatingAPI}Validating API settings...{/s}');

        Ext.Ajax.request({
            url: me.validateAPIUrl,
            params: {
                shopId: me.record.get('shopId'),
                clientId: generalSettings['clientId'],
                clientSecret: generalSettings['clientSecret'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onValidateAPIAjaxCallback, me)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onRegisterWebhookAjaxCallback: function(options, success, response) {
        var me = this;

        me.window.setLoading(false);

        if (success) {
            var responseObject = Ext.JSON.decode(response.responseText);
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/registerWebhookSuccess}The webhook has been successfully registered to:{/s} ' + responseObject['url'], me.window.title);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/registerWebhookError}Could not register webhook due to an unknown error.{/s}', me.window.title);
        }
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onValidateAPIAjaxCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            successFlag = responseObject.success;

        if (successFlag) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/validateAPISuccess}The API settings are valid.{/s}', me.window.title);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/validateAPIError}The API settings are invalid:{/s} ' + '<u>' + responseObject.message + '</u>', me.window.title);
        }

        me.window.setLoading(false);
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onDetailAjaxCallback: function(options, success, response) {
        var me = this;

        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Unified{/s}', '{s name=growl/loadSettingsError}Could not load settings due to an unknown error{/s}', me.window.title);
        }

        var generalTab = me.getGeneralTab(),
            paypalTab = me.getPaypalTab(),
            plusTab = me.getPlusTab(),
            installmentsTabs = me.getInstallmentsTab(),
            ecTab = me.getEcTab(),
            settings = Ext.JSON.decode(response.responseText)['settings'];

        me.record = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Settings', settings);

        // Update general tab
        generalTab.loadRecord(me.record);

        // Update the paypal tab
        paypalTab.loadRecord(me.record);

        // Update plus tab
        plusTab.loadRecord(me.record);

        // Update installments tab
        installmentsTabs.loadRecord(me.record);

        // Update express checkout tab
        ecTab.loadRecord(me.record);

        me.applyActivationState(me.record.get('active'));

        me.settingsSaved = false;
    },

    /**
     * A helper function that updates the UI depending on the activation state.
     *
     * @param { Boolean } active
     */
    applyActivationState: function(active) {
        var me = this;

        me.getGeneralTab().restContainer.setDisabled(!active);
        me.getGeneralTab().behaviorContainer.setDisabled(!active);

        me.getPaypalTab().setDisabled(!active);
        me.getPlusTab().setDisabled(!active);
        me.getInstallmentsTab().setDisabled(!active);
        me.getEcTab().setDisabled(!active);
    }
});
// {/block}
