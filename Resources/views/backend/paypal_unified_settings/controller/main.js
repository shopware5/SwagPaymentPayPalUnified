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
    payUponInvoiceDetailUrl: '{url controller=PaypalUnifiedPayUponInvoiceSettings action=detail}',

    /**
     * @type { String }
     */
    advancedCreditDebitCardDetailUrl: '{url controller=PaypalUnifiedAdvancedCreditDebitCardSettings action=detail}',

    /**
     * @type { String }
     */
    registerWebhookUrl: '{url controller=PaypalUnifiedSettings action=registerWebhook}',

    /**
     * @type { String }
     */
    validateAPIUrl: '{url controller=PaypalUnifiedSettings action=validateAPI}',

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
     * @type { Shopware.apps.PaypalUnifiedSettings.model.PayUponInvoice }
     */
    payUponInvoiceRecord: null,

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.model.AdvancedCreditDebitCard }
     */
    advancedCreditDebitCardRecord: null,

    /**
     * @type { XMLHttpRequest }
     */
    updateCredentialsRequest: null,

    /**
     * @type { Number }
     */
    shopId: null,

    refs: [
        { ref: 'generalTab', selector: 'paypal-unified-settings-tabs-general' },
        { ref: 'plusTab', selector: 'paypal-unified-settings-tabs-paypal-plus' },
        { ref: 'installmentsTab', selector: 'paypal-unified-settings-tabs-installments' },
        { ref: 'ecTab', selector: 'paypal-unified-settings-tabs-express-checkout' },
        { ref: 'payUponInvoiceTab', selector: 'paypal-unified-settings-tabs-pay-upon-invoice' },
        { ref: 'advancedCreditDebitCardTab', selector: 'paypal-unified-settings-tabs-advanced-credit-debit-card' },
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
                changeShop: me.onChangeShop
            },
            'paypal-unified-settings-toolbar': {
                saveSettings: me.onSaveSettings
            },
            'paypal-unified-settings-tabs-general': {
                registerWebhook: me.onRegisterWebhook,
                validateAPI: me.onValidateAPISettings,
                onChangeShopActivation: me.applyActivationState,
                onChangeMerchantLocation: me.applyMerchantLocationState,
                onInContextChange: me.onInContextChange,
                onChangeSandboxActivation: me.applySandboxActivationState,
                authCodeReceived: me.onAuthCodeReceivedGeneral
            },
            'paypal-unified-settings-tabs-pay-upon-invoice': {
                authCodeReceived: me.onAuthCodeReceivedPayUponInvoice
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
        me.loadSetting(me.payUponInvoiceDetailUrl);
        me.loadSetting(me.advancedCreditDebitCardDetailUrl);
    },

    loadSetting: function(detailUrl) {
        var me = this;

        me.applyActivationState(false);

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
        me.payUponInvoiceRecord.save();
        me.advancedCreditDebitCardRecord.save();
    },

    prepareRecords: function() {
        var me = this,
            generalTab = me.getGeneralTab(),
            plusTab = me.getPlusTab(),
            installmentsTab = me.getInstallmentsTab(),
            ecTab = me.getEcTab(),
            payUponInvoiceTab = me.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = me.getAdvancedCreditDebitCardTab();

        me.generalRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.General');
        me.expressCheckoutRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout');
        me.installmentsRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Installments');
        me.plusRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Plus');
        me.payUponInvoiceRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.PayUponInvoice');
        me.advancedCreditDebitCardRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.AdvancedCreditDebitCard');

        me.generalRecord.set('shopId', me.shopId);
        me.expressCheckoutRecord.set('shopId', me.shopId);
        me.installmentsRecord.set('shopId', me.shopId);
        me.plusRecord.set('shopId', me.shopId);
        me.payUponInvoiceRecord.set('shopId', me.shopId);
        me.advancedCreditDebitCardRecord.set('shopId', me.shopId);

        installmentsTab.loadRecord(me.installmentsRecord);
        generalTab.loadRecord(me.generalRecord);
        plusTab.loadRecord(me.plusRecord);
        ecTab.loadRecord(me.expressCheckoutRecord);
        payUponInvoiceTab.loadRecord(me.payUponInvoiceRecord);
        advancedCreditDebitCardTab.loadRecord(me.advancedCreditDebitCardRecord);
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
            generalTabForm = me.getGeneralTab().getForm(),
            generalSettings = generalTabForm.getValues(),
            plusSettings = me.getPlusTab().getForm().getValues(),
            installmentsSettings = me.getInstallmentsTab().getForm().getValues(),
            ecTabForm = me.getEcTab().getForm(),
            ecSettings = ecTabForm.getValues(),
            payUponInvoiceSettings = me.getPayUponInvoiceTab().getForm().getValues(),
            advancedCreditDebitCardSettings = me.getAdvancedCreditDebitCardTab().getForm().getValues();

        if (!generalTabForm.isValid() || !ecTabForm.isValid()) {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/formValidationError"}Please fill out all fields marked in red.{/s}', me.window.title);
            return;
        }

        me.window.setLoading('{s name="loading/saveSettings"}Saving settings...{/s}');

        me.generalRecord.set(generalSettings);
        me.expressCheckoutRecord.set(ecSettings);
        me.installmentsRecord.set(installmentsSettings);
        me.plusRecord.set(plusSettings);
        me.payUponInvoiceRecord.set(payUponInvoiceSettings);
        me.advancedCreditDebitCardRecord.set(advancedCreditDebitCardSettings);

        me.saveRecords();

        Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/saveSettings"}The settings have been saved!{/s}', me.window.title);

        me.onRegisterWebhook();
    },

    onRegisterWebhook: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name="loading/registeringWebhook"}Registering webhook...{/s}');

        Ext.Ajax.request({
            url: me.registerWebhookUrl,
            params: {
                shopId: me.shopId,
                clientId: generalSettings.clientId,
                clientSecret: generalSettings.clientSecret,
                sandboxClientId: generalSettings.sandboxClientId,
                sandboxClientSecret: generalSettings.sandboxClientSecret,
                sandbox: generalSettings.sandbox
            },
            callback: Ext.bind(me.onRegisterWebhookAjaxCallback, me)
        });
    },

    onValidateAPISettings: function() {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues();

        me.window.setLoading('{s name="loading/validatingAPI"}Validating API settings...{/s}');

        Ext.Ajax.request({
            url: me.validateAPIUrl,
            params: {
                shopId: me.shopId,
                clientId: generalSettings.clientId,
                clientSecret: generalSettings.clientSecret,
                sandboxClientId: generalSettings.sandboxClientId,
                sandboxClientSecret: generalSettings.sandboxClientSecret,
                sandbox: generalSettings.sandbox
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
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            message = '';

        me.window.setLoading(false);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/registerWebhookSuccess"}The webhook has been successfully registered to:{/s} ' + responseObject.url, me.window.title);
            return;
        }

        if (Ext.isDefined(responseObject)) {
            message = responseObject.message;
        }

        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name="growl/title"}PayPal{/s}',
                text: '{s name="growl/registerWebhookError"}Could not register webhook due this error:{/s}' + '<br><u>' + message + '</u>'
            },
            me.window.title
        );
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onValidateAPIAjaxCallback: function(options, success, response) {
        var me = this,
            responseObject = Ext.JSON.decode(response.responseText),
            message = '';

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/validateAPISuccess"}The API settings are valid.{/s}', me.window.title);
            me.window.setLoading(false);

            return;
        }

        if (Ext.isDefined(responseObject)) {
            message = responseObject.message;
        }

        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name="growl/title"}PayPal{/s}',
                text: '{s name="growl/validateAPIError"}The API settings are invalid:{/s} ' + '<br><u>' + message + '</u>'
            },
            me.window.title
        );

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
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/loadSettingsError"}Could not load settings due to an unknown error{/s}', me.window.title);
        }

        var generalTab = me.getGeneralTab(),
            plusTab = me.getPlusTab(),
            installmentsTab = me.getInstallmentsTab(),
            ecTab = me.getEcTab(),
            payUponInvoiceTab = me.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = me.getAdvancedCreditDebitCardTab(),
            settings = Ext.JSON.decode(response.responseText);

        if (settings.general) {
            me.generalRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.General', settings.general);
            generalTab.loadRecord(me.generalRecord);
            me.applyActivationState(me.generalRecord.get('active'));
            me.applySandboxActivationState(me.generalRecord.get('sandbox'));

            if (me.generalRecord.get('merchantLocation') === 'other') {
                plusTab.setDisabled(true);
                installmentsTab.setDisabled(true);
            } else {
                generalTab.smartPaymentButtonsCheckbox.setVisible(false);
            }
        } else if (settings.installments) {
            me.installmentsRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Installments', settings.installments);
            installmentsTab.loadRecord(me.installmentsRecord);
        } else if (settings.express) {
            me.expressCheckoutRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout', settings.express);
            ecTab.loadRecord(me.expressCheckoutRecord);
        } else if (settings.plus) {
            me.plusRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Plus', settings.plus);
            plusTab.loadRecord(me.plusRecord);
        } else if (settings['pay-upon-invoice']) {
            me.payUponInvoiceRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.PayUponInvoice', settings['pay-upon-invoice']);
            payUponInvoiceTab.loadRecord(me.payUponInvoiceRecord);

            payUponInvoiceTab.refreshTabItems();
        } else if (settings['advanced-credit-debit-card']) {
            me.advancedCreditDebitCardRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.AdvancedCreditDebitCard', settings['advanced-credit-debit-card']);
            advancedCreditDebitCardTab.loadRecord(me.advancedCreditDebitCardRecord);

            advancedCreditDebitCardTab.refreshTabItems();
        }

        me.settingsSaved = false;
    },

    /**
     * A helper function that updates the UI depending on the activation state.
     *
     * @param { Boolean } active
     */
    applyActivationState: function(active) {
        var me = this,
            generalTab = me.getGeneralTab();

        me.applyMerchantLocationState(generalTab.smartPaymentButtonsCheckbox);

        generalTab.restContainer.setDisabled(!active);
        generalTab.behaviourContainer.setDisabled(!active);
        generalTab.errorHandlingContainer.setDisabled(!active);

        me.getPlusTab().setDisabled(!active);
        me.getInstallmentsTab().setDisabled(!active);
        me.getEcTab().setDisabled(!active);
        me.getPayUponInvoiceTab().setDisabled(!active);
        me.getAdvancedCreditDebitCardTab().setDisabled(!active);

        me.generalRecord.set('active', active);
    },

    /**
     * A helper function that updates the UI depending on the sandbox activation state.
     *
     * @param { Boolean } active
     */
    applySandboxActivationState: function(active) {
        var me = this,
            generalTab = me.getGeneralTab(),
            payUponInvoiceTab = me.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = me.getAdvancedCreditDebitCardTab();

        generalTab.restLiveCredentialsContainer.setDisabled(active);
        generalTab.restSandboxCredentialsContainer.setDisabled(!active);

        generalTab.setSandbox(active);
        payUponInvoiceTab.setSandbox(active);
        advancedCreditDebitCardTab.setSandbox(active);

        generalTab.refreshOnboardingButton();
        payUponInvoiceTab.refreshTabItems();
        advancedCreditDebitCardTab.refreshTabItems();
    },

    applyMerchantLocationState: function(combobox) {
        var me = this,
            generalTab = me.getGeneralTab(),
            plusTab = me.getPlusTab(),
            installmentsTab = me.getInstallmentsTab(),
            payUponInvoiceTab = me.getPayUponInvoiceTab();

        if (combobox.value === 'other') {
            plusTab.setDisabled(true);
            installmentsTab.setDisabled(true);
            payUponInvoiceTab.setDisabled(true);
            me.plusRecord.set('active', false);
            me.installmentsRecord.set('active', false);
            me.payUponInvoiceRecord.set('active', false);
            generalTab.smartPaymentButtonsCheckbox.setVisible(true);
        } else {
            plusTab.setDisabled(false);
            installmentsTab.setDisabled(false);
            payUponInvoiceTab.setDisabled(false);
            generalTab.smartPaymentButtonsCheckbox.setVisible(false);
        }
    },

    onInContextChange: function(checkbox, styleFieldSet) {
        styleFieldSet.setDisabled(!checkbox.getValue());
    },

    /**
     * @param { String } authCode
     * @param { String } sharedId
     * @param { String } nonce
     * @param { String } partnerId
     */
    onAuthCodeReceivedGeneral: function (authCode, sharedId, nonce, partnerId) {
        var generalTab = this.getGeneralTab();

        this._onAuthCodeReceived({
            url: generalTab.getUpdateCredentialsUrl(),
            jsonData: {
                shopId: this.generalRecord.get('shopId'),
                authCode: authCode,
                sharedId: sharedId,
                nonce: nonce,
                sandbox: generalTab.getSandbox(),
                partnerId: partnerId
            }
        });
    },

    /**
     * @param { String } authCode
     * @param { String } sharedId
     * @param { String } nonce
     * @param { String } partnerId
     */
    onAuthCodeReceivedPayUponInvoice: function (authCode, sharedId, nonce, partnerId) {
        var payUponInvoiceTab = this.getPayUponInvoiceTab();

        this._onAuthCodeReceived({
            url: payUponInvoiceTab.getUpdateCredentialsUrl(),
            jsonData: {
                shopId: this.payUponInvoiceRecord.get('shopId'),
                authCode: authCode,
                sharedId: sharedId,
                nonce: nonce,
                sandbox: payUponInvoiceTab.getSandbox(),
                partnerId: partnerId
            }
        });
    },

    /**
     * @param { String } authCode
     * @param { String } sharedId
     * @param { String } nonce
     * @param { String } partnerId
     */
    onAuthCodeReceivedAdvancedCreditDebitCard: function (authCode, sharedId, nonce, partnerId) {
        var advancedCreditDebitCardTab = this.getAdvancedCreditDebitCardTab();

        this._onAuthCodeReceived({
            url: advancedCreditDebitCardTab.getUpdateCredentialsUrl(),
            jsonData: {
                shopId: this.advancedCreditDebitCardRecord.get('shopId'),
                authCode: authCode,
                sharedId: sharedId,
                nonce: nonce,
                sandbox: advancedCreditDebitCardTab.getSandbox(),
                partnerId: partnerId
            }
        });
    },

    /**
     * @param { Object } config
     * @param { string } config.url
     * @param { Object } config.jsonData
     */
    _onAuthCodeReceived: function (config) {
        if (this.updateCredentialsRequest !== null) {
            return;
        }

        config.success = this._onUpdateCredentialsSuccess.bind(this);
        config.failure = this._onUpdateCredentialsFailure.bind(this);
        config.callback = this._onUpdateCredentialsResponse.bind(this);

        if (!this.generalRecord.get('clientId') && !this.generalRecord.get('sandboxClientId')) {
            /**
             * Save the general record, just in case the server does not already
             * have a General-Settings-Entity where credentials have been
             * stored.
             */
            this.generalRecord.save();
        }

        this.updateCredentialsRequest = Ext.Ajax.request(config);
    },

    /**
     * @param { XMLHttpRequest } response
     * @param { Object } options
     *
     * @private
     */
    _onUpdateCredentialsSuccess: function (response, options) {
        this.loadSetting(this.generalDetailUrl);
        this.loadSetting(this.payUponInvoiceDetailUrl);
        this.loadSetting(this.advancedCreditDebitCardDetailUrl);

        PAYPAL.apps.Signup.MiniBrowser.win.close();
    },

    /**
     * @param { XMLHttpRequest } response
     * @param { Object } options
     *
     * @private
     */
    _onUpdateCredentialsFailure: function (response, options) {
        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name="growl/title"}PayPal{/s}',
                text: '{s name="growl/updateCredentialsFailure"}Your account information could not be updated automatically. Please repeat the authorisation process.{/s}'
            },
            me.window.title
        );
    },

    /**
     * Resets the updateCredentialsRequest reference after the corresponding
     * response has been received.
     *
     * @param { Object } options
     * @param { Boolean } success
     * @param { XMLHttpRequest } response
     *
     * @private
     */
    _onUpdateCredentialsResponse: function (options, success, response) {
        this.updateCredentialsRequest = null;
    }
});
// {/block}
