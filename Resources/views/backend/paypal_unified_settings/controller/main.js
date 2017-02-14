//{namespace name="backend/paypal_unified_settings/main"}
//{block name="backend/paypal_unified_settings/controller/main"}
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
     * @type { Shopware.apps.PaypalUnifiedSettings.model.Settings }
     */
    record: null,

    refs: [
        { ref: 'generalTab', selector: 'paypal-unified-settings-tabs-general' },
        { ref: 'plusTab', selector: 'paypal-unified-settings-tabs-paypal-plus' }
    ],

    init: function () {
        var me = this;

        me.createMainWindow();
        me.createComponentControl();

        me.callParent(arguments);
    },

    createComponentControl: function () {
        var me = this;

        me.control({
            'paypal-unified-settings-shop-selection': {
                'changeShop': me.onChangeShop
            },
            'paypal-unified-settings-toolbar': {
                'saveSettings': me.onSaveSettings
            }
        })
    },

    createMainWindow: function () {
        var me = this;
        me.window = me.getView('Window').create().show();
    },

    /**
     * @param { Number } shopId
     */
    loadDetails: function (shopId) {
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
    onChangeShop: function (record) {
        var me = this,
            shopId = record.get('id');

        me.loadDetails(shopId);
    },

    onSaveSettings: function () {
        var me = this,
            generalSettings = me.getGeneralTab().getForm().getValues(),
            plusSettings = me.getPlusTab().getForm().getValues();

        me.record.set(generalSettings);
        me.record.set(plusSettings);

        me.record.save();

        Shopware.Notification.createGrowlMessage('{s name=growl/saveSettings}The settings have been saved!{/s}');
    },

    /**
     * @param { Object } options
     * @param { Boolean } success
     * @param { Object } response
     */
    onDetailAjaxCallback: function (options, success, response)
    {
        if (!success) {
            Shopware.Notification.createGrowlMessage('{s name=growl/loadSettingsError}Could not load settings due to an unknown error{/s}')
        }

        var me = this,
            generalTab = me.getGeneralTab(),
            plusTab = me.getPlusTab(),
            settings = Ext.JSON.decode(response.responseText)['settings'];

        me.record  = Ext.create('Shopware.apps.PaypalUnifiedSettings.model.Settings', settings);

        //Update general tab
        generalTab.loadRecord(me.record);

        //Update plus tab
        plusTab.loadRecord(me.record);

        me.settingsSaved = false;
    }
});
//{/block}