// {namespace name="backend/paypal_unified_settings/toolbar"}
// {block name="backend/paypal_unified_settings/toolbar"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.paypal-unified-settings-toolbar',

    ui: 'shopware-ui',
    padding: '10 0 5',
    width: '100%',
    dock: 'bottom',

    initComponent: function () {
        var me = this;

        me.items = me.createItems();
        me.registerEvents();

        me.callParent(arguments);
    },

    registerEvents: function () {
        var me = this;

        me.addEvents(
            /**
             * This event will be triggered when the user clicks on the save button.
             */
            'saveSettings'
        );
    },

    /**
     * @returns { Array }
     */
    createItems: function () {
        var me = this,
            items = [];

        items.push('->'); // Right align the button.
        items.push(me.createSaveButton());

        return items;
    },

    /**
     * @returns { Shopware.apps.Base.view.element.Button }
     */
    createSaveButton: function () {
        var me = this;

        return Ext.create('Shopware.apps.Base.view.element.Button', {
            text: '{s name="button/save"}Save{/s}',
            cls: 'primary',
            handler: Ext.bind(me.onSaveButtonClick, me)
        });
    },

    onSaveButtonClick: function () {
        var me = this;

        me.fireEvent('saveSettings');
    }
});
// {/block}
