// {namespace name="backend/paypal_unified_settings/tabs/express_checkout"}
// {block name="backend/paypal_unified_settings/tabs/express_checkout"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.ExpressCheckout', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-express-checkout',
    title: '{s name=title}PayPal Express Checkout integration{/s}',

    anchor: '100%',
    bodyPadding: 10,
    border: false,

    style: {
        background: '#EBEDEF'
    },

    fieldDefaults: {
        anchor: '100%',
        labelWidth: 180
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var me = this;

        me.ecActivate = me.createEcActivate();
        me.ecDetailActivate = me.createEcDetailActivate();

        return [
            me.ecActivate,
            me.ecDetailActivate
        ];
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcActivate: function() {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'ecActive',
            fieldLabel: '{s name=field/activate}Activate PayPal EC{/s}',
            boxLabel: '{s name=field/activate/help}Activate in order to enable the PayPal Express Checkout integration for the selected shop.{/s}',
            inputValue: true,
            uncheckedValue: false,
            handler: Ext.bind(me.onActivateEc, me)
        });
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcDetailActivate: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'ecDetailActive',
            fieldLabel: '{s name=field/ecDetailActivate}Show on detail page{/s}',
            boxLabel: '{s name=field/ecDetailActivate/help}If this option is active, the Express Checkout button will be shown on each product detail page.{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true
        });
    },

    /**
     * @param { Shopware.apps.Base.view.element.Boolean } element
     * @param { Boolean } checked
     */
    onActivateEc: function(element, checked) {
        var me = this;

        me.ecDetailActivate.setDisabled(!checked);
    }
});
// {/block}
