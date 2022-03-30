// {namespace name="backend/paypal_unified_settings/main"}
// {block name="backend/paypal_unified_settings/controller/main"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.controller.Main', {
    extend: 'Enlight.app.Controller',

    PAYMENT_METHOD_CAPABILITY_NAME: {
        PAY_UPON_INVOICE: 'PAY_UPON_INVOICE',
        PAY_UPON_INVOICE_HAS_LIMITS: 'PAY_UPON_INVOICE_HAS_LIMITS',
        ADVANCED_CREDIT_DEBIT_CARD: 'CUSTOM_CARD_PROCESSING',
        ADVANCED_CREDIT_DEBIT_CARD_HAS_LIMITS: 'ADVANCED_CREDIT_DEBIT_CARD_HAS_LIMITS',
    },

    PRODUCT_SUBSCRIPTION_NAME: {
        PPCP: 'PPCP_STANDARD',
    },

    /**
     * @type { Shopware.apps.PaypalUnifiedSettings.view.Window }
     */
    window: null,

    /**
     * @type { String }
     */
    registerWebhookUrl: '{url controller=PaypalUnifiedSettings action=registerWebhook}',

    /**
     * @type { String }
     */
    validateAPIUrl: '{url controller=PaypalUnifiedSettings action=validateAPI}',

    /**
     * @type { String }
     */
    isCapableUrl: '{url controller=PaypalUnifiedSettings action=isCapable}',

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
        {
            ref: 'generalTab', selector: 'paypal-unified-settings-tabs-general'
        },
        {
            ref: 'plusTab', selector: 'paypal-unified-settings-tabs-paypal-plus'
        },
        {
            ref: 'installmentsTab', selector: 'paypal-unified-settings-tabs-installments'
        },
        {
            ref: 'ecTab', selector: 'paypal-unified-settings-tabs-express-checkout'
        },
        {
            ref: 'payUponInvoiceTab', selector: 'paypal-unified-settings-tabs-pay-upon-invoice'
        },
        {
            ref: 'advancedCreditDebitCardTab', selector: 'paypal-unified-settings-tabs-advanced-credit-debit-card'
        },
    ],

    init: function() {
        this.settingsLoader = Ext.create('Shopware.apps.PaypalUnifiedSettings.view.SettingsLoader', {
            dataPartialLoadedCallback: Ext.bind(this.partialDataLoaded, this),
            allDataLoadedCallback: Ext.bind(this.allDataLoaded, this),
            callbackScope: this
        });

        this.createMainWindow();
        this.createComponentControl();

        this.callParent(arguments);
    },

    createComponentControl: function() {
        this.control({
            'paypal-unified-settings-top-toolbar': {
                changeShop: this.onChangeShop
            },
            'paypal-unified-settings-toolbar': {
                saveSettings: this.onSaveSettings
            },
            'paypal-unified-settings-tabs-general': {
                registerWebhook: this.onRegisterWebhook,
                validateAPI: this.onValidateAPISettings,
                onChangeShopActivation: this.applyActivationState,
                onInContextChange: this.onInContextChange,
                onChangeSandboxActivation: this.applySandboxActivationState,
                authCodeReceived: this.onAuthCodeReceivedGeneral
            },
            'paypal-unified-settings-tabs-pay-upon-invoice': {
                authCodeReceived: this.onAuthCodeReceived,
                onTestCapability: this.onTestCapability
            },
            'paypal-unified-settings-tabs-advanced-credit-debit-card': {
                authCodeReceived: this.onAuthCodeReceived,
                onTestCapability: this.onTestCapability
            }
        });
    },

    createMainWindow: function() {
        this.window = this.getView('Window').create().show();
    },

    loadDetails: function() {
        this.applyActivationState(false);

        this.settingsLoader.loadSettings(this.shopId);
    },

    saveRecords: function() {
        var options = {
            callback: Ext.bind(this.afterSaveRecord, this)
        };

        this.saveCounter = 0;

        this.generalRecord.save(options);
        this.expressCheckoutRecord.save(options);
        this.installmentsRecord.save(options);
        this.plusRecord.save(options);
        this.payUponInvoiceRecord.save(options);
        this.advancedCreditDebitCardRecord.save(options);
    },

    afterSaveRecord: function() {
        this.saveCounter++;

        if (this.saveCounter < 6) {
            return;
        }

        this.loadFormRecords();

        this.allDataLoaded();

        Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/saveSettings"}The settings have been saved!{/s}', this.window.title);

        this.onRegisterWebhook();
    },

    prepareRecords: function() {
        this.generalRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.General');
        this.expressCheckoutRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout');
        this.installmentsRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Installments');
        this.plusRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Plus');
        this.payUponInvoiceRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.PayUponInvoice');
        this.advancedCreditDebitCardRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.AdvancedCreditDebitCard');

        this.generalRecord.set('shopId', this.shopId);
        this.expressCheckoutRecord.set('shopId', this.shopId);
        this.installmentsRecord.set('shopId', this.shopId);
        this.plusRecord.set('shopId', this.shopId);
        this.payUponInvoiceRecord.set('shopId', this.shopId);
        this.advancedCreditDebitCardRecord.set('shopId', this.shopId);

        this.loadFormRecords();
    },

    loadFormRecords: function() {
        this.getGeneralTab().loadRecord(this.generalRecord);
        this.getInstallmentsTab().loadRecord(this.installmentsRecord);
        this.getPlusTab().loadRecord(this.plusRecord);
        this.getEcTab().loadRecord(this.expressCheckoutRecord);
        this.getPayUponInvoiceTab().loadRecord(this.payUponInvoiceRecord);
        this.getAdvancedCreditDebitCardTab().loadRecord(this.advancedCreditDebitCardRecord);

        this.isTabFormValid(this.getPayUponInvoiceTab());
    },

    /**
     * @param { Shopware.data.Model } record
     */
    onChangeShop: function(record) {
        this.shopId = record.get('id');
        this.prepareRecords();
        this.loadDetails(this.shopId);
    },

    onSaveSettings: function() {
        var generalTabForm = this.getGeneralTab().getForm(),
            ecTabForm = this.getEcTab().getForm(),
            payUponInvoiceForm = this.getPayUponInvoiceTab().getForm(),
            sandbox = this.generalRecord.get('sandbox'),
            payerIdGetterKey = sandbox ? 'sandboxPaypalPayerId' : 'paypalPayerId';

        if (!this.isTabFormValid(this.getGeneralTab()) || !this.isTabFormValid(this.getEcTab()) || !this.isTabFormValid(this.getPayUponInvoiceTab())) {
            return;
        }

        this.window.setLoading('{s name="loading/saveSettings"}Saving settings...{/s}');

        this.generalRecord.set(generalTabForm.getValues());
        this.expressCheckoutRecord.set(ecTabForm.getValues());
        this.installmentsRecord.set(this.getInstallmentsTab().getForm().getValues());
        this.plusRecord.set(this.getPlusTab().getForm().getValues());
        this.payUponInvoiceRecord.set(payUponInvoiceForm.getValues());
        this.advancedCreditDebitCardRecord.set(this.getAdvancedCreditDebitCardTab().getForm().getValues());

        var payerId = this.generalRecord.get(payerIdGetterKey);

        this.saveRecords();

        if (payerId.trim() !== '') {
            this.checkBothCapabilities(sandbox, payerId);

            return;
        }
    },

    /**
     * @param tab { Ext.form.Panel }
     */
    isTabFormValid: function(tab) {
        var form = tab.getForm(),
            isValid = form.isValid();

        if (!isValid) {
            this.window.tabContainer.setActiveTab(tab);
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/formValidationError"}Please fill out all fields marked in red.{/s}', this.window.title);
        }

        return isValid;
    },

    /**
     * @param sandbox { Boolean }
     * @param payerId { String }
     */
    checkBothCapabilities: function(sandbox, payerId) {
        var paymentMethodCapabilityNames = [
            this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE,
            this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD
        ]

        var productSubscriptionNames = [
            this.PRODUCT_SUBSCRIPTION_NAME.PPCP,
        ];

        this.checkIsCapable(sandbox, payerId, paymentMethodCapabilityNames, productSubscriptionNames, this.onBeforeSaveSettings, this);
    },

    /**
     * @param sandbox { Boolean }
     * @param payerId { String }
     * @param paymentMethodCapabilityNames { Array }
     * @param productSubscriptionNames { Array }
     * @param callback { Function }
     * @param scope { Object }
     */
    checkIsCapable: function(sandbox, payerId, paymentMethodCapabilityNames, productSubscriptionNames, callback, scope) {
        Ext.Ajax.request({
            url: this.isCapableUrl,
            jsonData: {
                shopId: this.shopId,
                sandbox: sandbox,
                payerId: payerId,
                paymentMethodCapabilityNames: paymentMethodCapabilityNames,
                productSubscriptionNames: productSubscriptionNames,
            },
            callback: Ext.bind(callback, scope)
        });
    },

    /**
     * @param request { Object }
     * @param success { Boolean }
     * @param response { Object }
     */
    onBeforeSaveSettings: function(request, success, response) {
        var responseBody = Ext.JSON.decode(response.responseText),
            sandbox = this.generalRecord.get('sandbox'),
            setterName = sandbox ? 'sandboxOnboardingCompleted' : 'onboardingCompleted';

        if (!responseBody.success) {
            Shopware.Notification.createGrowlMessage(
                '{s name="growl/title"}PayPal{/s}',
                '{s name="growl/saveSettingsError"}Could not save settings due to an error:{/s} ' + responseBody.message,
                this.window.title
            );

            this.window.setLoading(false);

            return;
        }

        if (responseBody[this.PRODUCT_SUBSCRIPTION_NAME.PPCP]) {
            this.promptDisablePlus();
        }

        // If it is "NOT isEligible" and "isActive".. Then set active to the eligibility result
        if (!responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE] && this.payUponInvoiceRecord.get('active')) {
            this.payUponInvoiceRecord.set('active', responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE]);
        }
        if (!responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD] && this.advancedCreditDebitCardRecord.get('active')) {
            this.advancedCreditDebitCardRecord.set('active', responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD]);
        }

        // If onboarding "NOT completed" set active to the eligibility result
        if (!this.payUponInvoiceRecord.get(setterName)) {
            this.payUponInvoiceRecord.set('active', responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE]);
        }
        if (!this.advancedCreditDebitCardRecord.get(setterName)) {
            this.advancedCreditDebitCardRecord.set('active', responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD]);
        }

        // Set onboardingCompleted to the eligibility result
        this.advancedCreditDebitCardRecord.set(setterName, responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD]);
        this.payUponInvoiceRecord.set(setterName, responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE]);

        // Set limits result
        this.getPayUponInvoiceTab().setHasLimits(responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE_HAS_LIMITS])
        this.getAdvancedCreditDebitCardTab().setHasLimits(responseBody[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD_HAS_LIMITS])

        this.saveRecords();
    },

    onRegisterWebhook: function() {
        var generalSettings = this.getGeneralTab().getForm().getValues();

        this.window.setLoading('{s name="loading/registeringWebhook"}Registering webhook...{/s}');

        Ext.Ajax.request({
            url: this.registerWebhookUrl,
            params: {
                shopId: this.shopId,
                clientId: generalSettings.clientId,
                clientSecret: generalSettings.clientSecret,
                sandboxClientId: generalSettings.sandboxClientId,
                sandboxClientSecret: generalSettings.sandboxClientSecret,
                sandbox: generalSettings.sandbox
            },
            callback: Ext.bind(this.onRegisterWebhookAjaxCallback, this)
        });
    },

    onValidateAPISettings: function() {
        var generalSettings = this.getGeneralTab().getForm().getValues();

        this.window.setLoading('{s name="loading/validatingAPI"}Validating API settings...{/s}');

        Ext.Ajax.request({
            url: this.validateAPIUrl,
            params: {
                shopId: this.shopId,
                clientId: generalSettings.clientId,
                clientSecret: generalSettings.clientSecret,
                sandboxClientId: generalSettings.sandboxClientId,
                sandboxClientSecret: generalSettings.sandboxClientSecret,
                sandbox: generalSettings.sandbox
            },
            callback: Ext.bind(this.onValidateAPIAjaxCallback, this)
        });
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onRegisterWebhookAjaxCallback: function(options, success, response) {
        var responseObject = Ext.JSON.decode(response.responseText),
            message = '';

        this.window.setLoading(false);

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/registerWebhookSuccess"}The webhook has been successfully registered to:{/s} ' + responseObject.url, this.window.title);
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
            this.window.title
        );
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onValidateAPIAjaxCallback: function(options, success, response) {
        var responseObject = Ext.JSON.decode(response.responseText),
            message = '';

        if (Ext.isDefined(responseObject) && responseObject.success) {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/validateAPISuccess"}The API settings are valid.{/s}', this.window.title);
            this.window.setLoading(false);

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
            this.window.title
        );

        this.window.setLoading(false);
    },

    /**
     * @param { Object } request
     * @param { Boolean } success
     * @param { Object } response
     */
    partialDataLoaded: function(request, success, response) {
        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/loadSettingsError"}Could not load settings due to an unknown error{/s}', this.window.title);
        }

        var generalTab = this.getGeneralTab(),
            plusTab = this.getPlusTab(),
            installmentsTab = this.getInstallmentsTab(),
            ecTab = this.getEcTab(),
            payUponInvoiceTab = this.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = this.getAdvancedCreditDebitCardTab(),
            settings = Ext.JSON.decode(response.responseText);

        if (settings.general) {
            this.generalRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.General', settings.general);
            generalTab.loadRecord(this.generalRecord);
            this.applyActivationState(this.generalRecord.get('active'));
            this.applySandboxActivationState(this.generalRecord.get('sandbox'));
        } else if (settings.installments) {
            this.installmentsRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Installments', settings.installments);
            installmentsTab.loadRecord(this.installmentsRecord);
        } else if (settings.express) {
            this.expressCheckoutRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.ExpressCheckout', settings.express);
            ecTab.loadRecord(this.expressCheckoutRecord);
        } else if (settings.plus) {
            this.plusRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Plus', settings.plus);
            plusTab.loadRecord(this.plusRecord);
        } else if (settings.payUponInvoice) {
            this.payUponInvoiceRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.PayUponInvoice', settings.payUponInvoice);
            payUponInvoiceTab.loadRecord(this.payUponInvoiceRecord);
        } else if (settings.advancedCreditDebitCard) {
            this.advancedCreditDebitCardRecord = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.AdvancedCreditDebitCard', settings.advancedCreditDebitCard);
            advancedCreditDebitCardTab.loadRecord(this.advancedCreditDebitCardRecord);
        }
    },

    allDataLoaded: function() {
        var generalTab = this.getGeneralTab(),
            plusTab = this.getPlusTab(),
            payUponInvoiceTab = this.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = this.getAdvancedCreditDebitCardTab(),
            isSandBox = false,
            generalSettingGetterKey = 'paypalPayerId',
            payerId;

        generalTab.sandboxPayerIdNotice.hide();
        generalTab.payerIdNotice.hide();

        payUponInvoiceTab.handleView();
        advancedCreditDebitCardTab.handleView();

        if (!this.generalRecord.get('active')) {
            return;
        }

        if (this.generalRecord.get('sandbox')) {
            isSandBox = true;
            generalSettingGetterKey = 'sandboxPaypalPayerId';
        }

        payerId = this.generalRecord.get(generalSettingGetterKey);
        if ((payUponInvoiceTab.isPaymentMethodActive() || advancedCreditDebitCardTab.isPaymentMethodActive()) && payerId.trim() === '') {
            if (isSandBox) {
                generalTab.sandboxPayerIdNotice.show();
                return;
            }

            generalTab.payerIdNotice.show();
        }

        if (this._shouldPlusTabBeDisabled()) {
            plusTab.setDisabled(true);
        }
    },

    /**
     * A helper function that updates the UI depending on the activation state.
     *
     * @param { Boolean } active
     */
    applyActivationState: function(active) {
        var generalTab = this.getGeneralTab();

        generalTab.restContainer.setDisabled(!active);
        generalTab.behaviourContainer.setDisabled(!active);
        generalTab.errorHandlingContainer.setDisabled(!active);

        this.getPlusTab().setDisabled(!active || this._shouldPlusTabBeDisabled());
        this.getInstallmentsTab().setDisabled(!active);
        this.getEcTab().setDisabled(!active);
        this.getPayUponInvoiceTab().setDisabled(!active);
        this.getAdvancedCreditDebitCardTab().setDisabled(!active);

        this.generalRecord.set('active', active);

        this.allDataLoaded();
    },

    /**
     * A helper function that updates the UI depending on the sandbox activation state.
     *
     * @param { Boolean } active
     */
    applySandboxActivationState: function(active) {
        var generalTab = this.getGeneralTab(),
            payUponInvoiceTab = this.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = this.getAdvancedCreditDebitCardTab();

        generalTab.restLiveCredentialsContainer.setDisabled(active);
        generalTab.restSandboxCredentialsContainer.setDisabled(!active);

        generalTab.setSandbox(active);
        generalTab.refreshOnboardingButton();

        payUponInvoiceTab.setSandbox(active);
        payUponInvoiceTab.refreshOnboardingButton(payUponInvoiceTab.buttonValue);

        advancedCreditDebitCardTab.setSandbox(active);
        advancedCreditDebitCardTab.refreshOnboardingButton(advancedCreditDebitCardTab.buttonValue);

        this.generalRecord.set('sandbox', active);
        this.allDataLoaded();
    },

    /**
     * @param checkbox { Ext.form.field.Checkbox }
     * @param styleFieldSet { Ext.form.FieldSet }
     */
    onInContextChange: function(checkbox, styleFieldSet) {
        styleFieldSet.setDisabled(!checkbox.getValue());
    },

    /**
     * @param { String } authCode
     * @param { String } sharedId
     * @param { String } nonce
     * @param { String } partnerId
     */
    onAuthCodeReceivedGeneral: function(authCode, sharedId, nonce, partnerId) {
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
     * @param { Ext.button.Button } button
     * @param { String } authCode
     * @param { String } sharedId
     * @param { String } nonce
     * @param { String } partnerId
     */
    onAuthCodeReceived: function(authCode, sharedId, nonce, partnerId, buttonValue) {
        var tab, record;

        if (buttonValue === this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE) {
            tab = this.getPayUponInvoiceTab();
            record = this.payUponInvoiceRecord;
        }

        if (buttonValue === this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD) {
            tab = this.getAdvancedCreditDebitCardTab();
            record = this.advancedCreditDebitCardRecord;
        }

        if (buttonValue === 'GENERAL') {
            tab = this.getGeneralTab();
            record = this.generalRecord;
        }

        this._onAuthCodeReceived({
            url: tab.getUpdateCredentialsUrl(),
            jsonData: {
                shopId: record.get('shopId'),
                authCode: authCode,
                sharedId: sharedId,
                nonce: nonce,
                sandbox: tab.getSandbox(),
                partnerId: partnerId
            }
        });
    },

    /**
     * @param button { Ext.button.Button }
     */
    onTestCapability: function(button) {
        var sandbox = this.generalRecord.get('sandbox'),
            payerId = this.generalRecord.get(sandbox ? 'sandboxPaypalPayerId' : 'paypalPayerId');

        if (payerId.trim() === '') {
            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/payerIdRequired"}A PayPal Merchant ID is required.{/s}', this.window.title);
            return;
        }

        this.window.setLoading(true);

        this.checkIsCapable(
            sandbox,
            payerId,
            [this.PAYMENT_METHOD_CAPABILITY_NAME[button.name]],
            [],
            Ext.bind(this.afterCallCapability, this),
            this
        )
    },

    /**
     * @param { Object } request
     * @param { Boolean } success
     * @param { Object } response
     */
    afterCallCapability: function(request, success, response) {
        var me = this,
            responseJson = Ext.JSON.decode(response.responseText),
            sandbox = this.generalRecord.get('sandbox'),
            property = sandbox ? 'sandboxOnboardingCompleted' : 'onboardingCompleted',
            payUponInvoiceTab = me.getPayUponInvoiceTab(),
            advancedCreditDebitCardTab = this.getAdvancedCreditDebitCardTab(),
            newValue;

        if (responseJson.success === false) {
            this.window.setLoading(false);

            Shopware.Notification.createGrowlMessage('{s name="growl/title"}PayPal{/s}', '{s name="growl/apiError"}An error occurred while check the capabilities{/s} ' + responseJson.message, this.window.title);
            return;
        }

        if (responseJson.hasOwnProperty(this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE)) {
            newValue = responseJson[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE];
            payUponInvoiceTab.setHasLimits(responseJson[this.PAYMENT_METHOD_CAPABILITY_NAME.PAY_UPON_INVOICE_HAS_LIMITS])
            this.payUponInvoiceRecord.set(property, newValue);
            this.payUponInvoiceRecord.save({
                callback: function(record) {
                    payUponInvoiceTab.loadRecord(record);
                    me.window.setLoading(false);

                    Shopware.Notification.createGrowlMessage(
                        '{s name="growl/title"}PayPal{/s}',
                        newValue ? '{s name="growl/capability/success"}The payment method is activated.{/s}' : '{s name="growl/capability/failed"}Unfortunately the payment method is not activated. Please repeat the onboarding.{/s}',
                        me.window.title
                    );
                }
            });
        }

        if (responseJson.hasOwnProperty(this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD)) {
            newValue = responseJson[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD];
            advancedCreditDebitCardTab.setHasLimits(responseJson[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD_HAS_LIMITS])
            this.advancedCreditDebitCardRecord.set(property, responseJson[this.PAYMENT_METHOD_CAPABILITY_NAME.ADVANCED_CREDIT_DEBIT_CARD]);
            this.advancedCreditDebitCardRecord.save(
                {
                    callback: function(record) {
                        advancedCreditDebitCardTab.loadRecord(record);
                        me.window.setLoading(false);

                        Shopware.Notification.createGrowlMessage(
                            '{s name="growl/title"}PayPal{/s}',
                            newValue ? '{s name="growl/capability/success"}The payment method is activated.{/s}' : '{s name="growl/capability/failed"}Unfortunately the payment method is not activated. Please repeat the onboarding.{/s}',
                            me.window.title
                        );
                    }
                }
            );
        }
    },

    promptDisablePlus: function() {
        var sandbox = this.generalRecord.get('sandbox'),
            isPlusActive = this.plusRecord.get('active'),
            isSubscribedAlready = this.plusRecord.get(sandbox ? 'sandboxPpcpActive' : 'ppcpActive'),
            buttonTextConfig = {
                ok: '{s name="prompt/disable_plus/button/ok"}{/s}'
            };

        if (isSubscribedAlready) {
            return;
        }

        if (isPlusActive) {
            Object.assign(buttonTextConfig, {
                cancel: '{s name="prompt/disable_plus/button/cancel"}{/s}'
            });

            Ext.MessageBox.show({
                title: '{s name="prompt/disable_plus/title"}{/s}',
                msg: isPlusActive ? '{s name="prompt/disable_plus/message/active"}{/s}' : '{s name="prompt/disable_plus/message/inactive"}{/s}',
                ui: 'shopware-ui',
                closable: false,
                buttons: isPlusActive ? Ext.MessageBox.OKCANCEL : Ext.MessageBox.OK,
                fn: Ext.bind(this.promptDisablePlusHandler, this),
                buttonText: buttonTextConfig
            });
        } else {
            this.promptDisablePlusHandler('ok');
        }
    },

    promptDisablePlusHandler: function(buttonId) {
        var sandbox = this.generalRecord.get('sandbox');

        this.plusRecord.set(sandbox ? 'sandboxPpcpActive' : 'ppcpActive', true);

        if (buttonId === 'ok') {
            this.plusRecord.set('active', false);
        }

        this.plusRecord.save({
            callback: Ext.bind(this.afterSaveRecord, this)
        });
    },

    /**
     * @param { Object } config
     * @param { string } config.url
     * @param { Object } config.jsonData
     */
    _onAuthCodeReceived: function(config) {
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
    _onUpdateCredentialsSuccess: function(response, options) {
        this.loadDetails();

        PAYPAL.apps.Signup.MiniBrowser.win.close();
    },

    /**
     * @param { XMLHttpRequest } response
     * @param { Object } options
     *
     * @private
     */
    _onUpdateCredentialsFailure: function(response, options) {
        Shopware.Notification.createStickyGrowlMessage(
            {
                title: '{s name="growl/title"}PayPal{/s}',
                text: '{s name="growl/updateCredentialsFailure"}Your account information could not be updated automatically. Please repeat the authorisation process.{/s}'
            },
            this.window.title
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
    _onUpdateCredentialsResponse: function(options, success, response) {
        this.updateCredentialsRequest = null;
    },

    _shouldPlusTabBeDisabled: function() {
        return !this.plusRecord.get('active') &&
            (this.generalRecord.get('sandbox') ? this.plusRecord.get('sandboxPpcpActive') : this.plusRecord.get('ppcpActive'));
    }
});
// {/block}
