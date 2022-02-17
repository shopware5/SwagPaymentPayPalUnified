// {namespace name="backend/paypal_unified_settings/tabs/settings_loader"}
// {block name="backend/paypal_unified_settings/settings_loader"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.SettingsLoader', {
    extend: 'Ext.Component',

    /**
     * @type { Function }
     */
    dataPartialLoadedCallback: null,

    /**
     * @type { Function }
     */
    allDataLoadedCallback: null,

    /**
     * @type { Object }
     */
    callbackScope: null,

    /**
     * @type { Object }
     */
    urls: {
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
    },

    /**
     * @type { Object }
     */
    isDataLoadedFromUrl: {
        /**
         * @type { Boolean }
         */
        generalDetailUrl: false,

        /**
         * @type { Boolean }
         */
        installmentsDetailUrl: false,

        /**
         * @type { Boolean }
         */
        expressDetailUrl: false,

        /**
         * @type { Boolean }
         */
        plusDetailUrl: false,

        /**
         * @type { Boolean }
         */
        payUponInvoiceDetailUrl: false,

        /**
         * @type { Boolean }
         */
        advancedCreditDebitCardDetailUrl: false,
    },

    constructor: function() {
        this.callParent(arguments);

        if (!this.dataPartialLoadedCallback) {
            throw new Error('Function dataPartialLoadedCallback is required')
        }

        if (!this.allDataLoadedCallback) {
            throw new Error('Function allDataLoadedCallback is required')
        }

        if (!this.callbackScope) {
            throw new Error('Object callbackScope is required')
        }
    },

    /**
     * @param shopId { Number }
     */
    loadSettings: function(shopId) {
        this.resetIsDataLoadedFromUrl();

        Ext.Object.each(this.urls, Ext.bind(this.loadUrl, this, shopId, true))
    },

    /**
     * @param key { String }
     * @param url { String }
     * @param urls { Object }
     * @param shopId { Number }
     */
    loadUrl: function(key, url, urls, shopId) {
        this.load(url, shopId, key)
    },

    /**
     *
     * @param url { String }
     * @param shopId { Number }
     * @param key { String }
     */
    load: function(url, shopId, key) {
        Ext.Ajax.request({
            url: url,
            params: {
                shopId: shopId
            },
            callback: Ext.bind(this.afterLoad, this, key, true)
        });
    },

    /**
     * @param request { Object }
     * @param success { Boolean }
     * @param response { Object }
     * @param key { String }
     */
    afterLoad: function(request, success, response, key) {
        this.isDataLoadedFromUrl[key] = true;

        this.dataPartialLoadedCallback.call(this.callbackScope, request, success, response);

        if (!this.isAllDataLoaded()) {
            return;
        }

        this.allDataLoadedCallback.call(this.callbackScope);
    },

    /**
     * @return { boolean }
     */
    isAllDataLoaded: function() {
        var allLoaded = true;

        Ext.Object.each(this.isDataLoadedFromUrl, function(key, value) {
            if (value === false) {
                allLoaded = false;
            }
        });

        return allLoaded;
    },

    resetIsDataLoadedFromUrl: function() {
        var me = this;

        Ext.Object.each(this.isDataLoadedFromUrl, function(key) {
            me.isDataLoadedFromUrl[key] = false;
        });
    },
});
// {/block}
