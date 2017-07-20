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
    generalDetailUrl: '{url controller=PaypalUnifiedGeneralSettings action=detail}',

    /**
     * @type { String }
     */
    installmentsDetailUrl: '{url controller=PaypalUnifiedInstallmentsSettings action=detail}',

    /**
     * @type { String }
     */
    expressDetailUrl: '{url controller=PaypalUnifiedExpressSettings action=detail}',

    /**
     * @type { String }
     */
    plusDetailUrl: '{url controller=PaypalUnifiedPlusSettings action=detail}',

    /**
     * @type { String }
     */
    registerWebhookUrl: '{url controller=PaypalUnifiedSettings action=registerWebhook}',

    /**
     * @type { String }
     */
    validateAPIUrl: '{url controller=PaypalUnifiedSettings action=validateAPI}',

    /**
     * @type { string }
     */
    testInstallmentsAvailabilityUrl: '{url controller=PaypalUnifiedSettings action=testInstallmentsAvailability}',

    /**
     * @type { string }
     */
    createWebProfilesUrl: '{url controller=PaypalUnifiedSettings action=createWebProfiles}',

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.model.General }
     */
    generalRecord: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.model.Installments }
     */
    installmentsRecord: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout }
     */
    expressCheckoutRecord: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.model.Plus }
     */
    plusRecord: null,

    /**
     * @type { Number }
     */
    shopId: null,

    refs: [
        { ref: 'generalTab', selector: 'paypal-unified-settings-tabs-general' },
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
            },
            'paypal-unified-settings-tabs-installments': {
                'testInstallmentsAvailability': me.onTestInstallmentsAvailability
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

        me.shopId = shopId;

        me.prepareRecords();

        me.loadSetting(me.generalDetailUrl);
        me.loadSetting(me.expressDetailUrl);
        me.loadSetting(me.installmentsDetailUrl);
        me.loadSetting(me.plusDetailUrl);
    },

    loadSetting: function(detailUrl) {
        var me = this;

        Ext.Ajax.request({
            url: detailUrl,
            params: {
                shopId: me.shopId
            },
            callback: Ext.bind(me.onDetailAjaxCallback, me)
        });
    },

    saveRecords: function() {
        var me = this;

        me.generalRecord.save();
        me.expressCheckoutRecord.save();
        me.installmentsRecord.save();
        me.plusRecord.save();
    },

    prepareRecords: function() {
        var me = this,
            generalTab = me.getGeneralTab(),
            plusTab = me.getPlusTab(),
            installmentsTab = me.getInstallmentsTab(),
            ecTab = me.getEcTab();

        me.generalRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.General');
        me.expressCheckoutRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout');
        me.installmentsRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Installments');
        me.plusRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Plus');

        me.generalRecord.set('shopId', me.shopId);
        me.expressCheckoutRecord.set('shopId', me.shopId);
        me.installmentsRecord.set('shopId', me.shopId);
        me.plusRecord.set('shopId', me.shopId);

        installmentsTab.loadRecord(me.installmentsRecord);
        generalTab.loadRecord(me.generalRecord);
        plusTab.loadRecord(me.plusRecord);
        ecTab.loadRecord(me.expressCheckoutRecord);
    },

    createWebProfiles: function () {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        Ext.Ajax.request({
            url: me.createWebProfilesUrl,
            params: {
                shopId: me.shopId,
                clientId: generalSettings['clientId'],
                clientSecret: generalSettings['clientSecret'],
                sandbox: generalSettings['sandbox'],
                brandName: generalSettings['brandName'],
                logoImage: generalSettings['logoImage']
            },
            callback: Ext.bind(me.onCreateWebProfilesAjaxCallback, me)
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
            plusSettings = me.getPlusTab().getForm().getValues(),
            installmentsSettings = me.getInstallmentsTab().getForm().getValues(),
            ecSettings = me.getEcTab().getForm().getValues();

        if (!me.getGeneralTab().getForm().isValid()) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/formValidationError}Please fill out all fields marked in red.{/s}', me.window.title);
            return;
        }

        me.window.setLoading('{s name="loading/saveSettings"}Saving settings...{/s}');

        me.generalRecord.set(generalSettings);
        me.expressCheckoutRecord.set(ecSettings);
        me.installmentsRecord.set(installmentsSettings);
        me.plusRecord.set(plusSettings);

        me.saveRecords();

        Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/saveSettings}The settings have been saved!{/s}', me.window.title);

        me.createWebProfiles();
    },

    onRegisterWebhook: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name="loading/registeringWebhook"}Registering webhook...{/s}');

        Ext.Ajax.request({
            url: me.registerWebhookUrl,
            params: {
                shopId: me.shopId,
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
                shopId: me.shopId,
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
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/registerWebhookSuccess}The webhook has been successfully registered to:{/s} ' + responseObject['url'], me.window.title);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/registerWebhookError}Could not register webhook due to an unknown error.{/s}', me.window.title);
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
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/validateAPISuccess}The API settings are valid.{/s}', me.window.title);
        } else {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/validateAPIError}The API settings are invalid:{/s} ' + '<u>' + responseObject.message + '</u>', me.window.title);
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
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/loadSettingsError}Could not load settings due to an unknown error{/s}', me.window.title);
        }

        var generalTab = me.getGeneralTab(),
            plusTab = me.getPlusTab(),
            installmentsTab = me.getInstallmentsTab(),
            ecTab = me.getEcTab(),
            settings = Ext.JSON.decode(response.responseText);

        if (settings.general) {
            me.generalRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.General', settings.general);
            generalTab.loadRecord(me.generalRecord);
            me.applyActivationState(me.generalRecord.get('active'));
        } else if (settings.installments) {
            me.installmentsRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Installments', settings.installments);
            installmentsTab.loadRecord(me.installmentsRecord);
        } else if (settings.express) {
            me.expressCheckoutRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout', settings.express);
            ecTab.loadRecord(me.expressCheckoutRecord);
        } else if (settings.plus) {
            me.plusRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Plus', settings.plus);
            plusTab.loadRecord(me.plusRecord);
        }

        me.settingsSaved = false;
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onCreateWebProfilesAjaxCallback: function(options, success, response) {
        var me = this;

        me.window.setLoading(false);
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
        me.getGeneralTab().errorHandlingContainer.setDisabled(!active);

        me.getPlusTab().setDisabled(!active);
        me.getInstallmentsTab().setDisabled(!active);
        me.getEcTab().setDisabled(!active);
    },

    onTestInstallmentsAvailability: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name=loading/testInstallments}Test installments availability...{/s}');

        Ext.Ajax.request({
            url: me.testInstallmentsAvailabilityUrl,
            params: {
                shopId: me.shopId,
                clientId: generalSettings['clientId'],
                clientSecret: generalSettings['clientSecret'],
                sandbox: generalSettings['sandbox']
            },
            callback: Ext.bind(me.onTestInstallmentsAvailabilityCallback, me)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onTestInstallmentsAvailabilityCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            successFlag = responseObject.success,
            errorMessageText;

        if (successFlag) {
            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', '{s name=growl/testInstallmentsAvailabilitySuccess}PayPal installments integration is working correct.{/s}', me.window.title);
        } else {
            errorMessageText = '{s name=growl/testInstallmentsAvailabilitySuccessError}PayPal installments integration is currently not available for you. Please contact the PayPal support.{/s} ';
            if (responseObject.message) {
                errorMessageText += '<br>ErrorMessage:<br><u>' + responseObject.message + '</u>';
            }

            Shopware.Notification.createGrowlMessage('{s name=growl/title}PayPal Products{/s}', errorMessageText, me.window.title);
        }

        me.window.setLoading(false);
    }
});
// {/block}
