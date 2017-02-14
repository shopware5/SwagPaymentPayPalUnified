//{namespace name="backend/paypal_unified_settings/shop_selection"}
//{block name="backend/paypal_unified_settings/shop_selection"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.ShopSelection', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.paypal-unified-settings-shop-selection',

    ui: 'shopware-ui',
    padding: '5 0 5',
    width: '100%',
    dock: 'top',


    initComponent: function () {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    registerEvents: function() {
        var me = this;

        me.addEvents(
            /**
             * Will be fired if the user changed the selected shop.
             *
             * @param { Shopware.apps.Base.model.Shop } data
             */
            'changeShop'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = [];

        items.push('->'); //Right align the selection.
        items.push(me.createShopSelection());

        return items;
    },

    /**
     * @returns { Shopware.apps.Base.view.element.Select }
     */
    createShopSelection: function () {
        var me = this,
            store = Ext.create('Shopware.apps.Base.store.Shop'),
            shopCombobox;

        store.filters.clear();

        store.load({
            callback: function (records) {
                shopCombobox.setValue(records[0].get('id')); //Set the default selection to the first entry
                me.fireEvent('changeShop', records[0])
            }
        });

        shopCombobox = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=label/shop}Select shop{/s}',
            labelWidth: 80,
            store: store,
            queryMode: 'local',
            valueField: 'id',
            editable: false,
            displayField: 'name',

            listeners: {
                select: Ext.bind(me.onSelectShop, me)
            }
        });

        return shopCombobox;
    },

    /**
     * @param { Ext.form.field.ComboBox } element
     * @param { Shopware.apps.Base.model.Shop } record
     */
    onSelectShop: function (element, record) {
        var me = this;

        if (record[0]) {
            me.fireEvent('changeShop', record[0])
        }
    }
});
//{/block}