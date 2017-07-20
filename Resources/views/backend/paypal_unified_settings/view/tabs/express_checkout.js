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

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecActivate: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecDetailActivate: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecButtonStyleColor: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecButtonStyleShape: null,

    /**
     * @type { Ext.form.field.ComboBox }
     */
    ecButtonStyleSize: null,

    /**
     * @type { Ext.form.field.Checkbox }
     */
    ecSubmitCart: null,

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
        me.ecButtonStyleColor = me.createEcButtonStyleColor();
        me.ecButtonStyleShape = me.createEcButtonStyleShape();
        me.ecButtonStyleSize = me.createEcButtonStyleSize();
        me.ecSubmitCart = me.createEcSubmitCart();

        return [
            me.ecActivate,
            me.ecDetailActivate,
            me.ecButtonStyleColor,
            me.ecButtonStyleShape,
            me.ecButtonStyleSize,
            me.ecSubmitCart
        ];
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcActivate: function() {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'active',
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
            name: 'detailActive',
            fieldLabel: '{s name=field/ecDetailActivate}Show on detail page{/s}',
            boxLabel: '{s name=field/ecDetailActivate/help}If this option is active, the Express Checkout button will be shown on each product detail page.{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true
        });
    },

    /**
     * @returns { Ext.form.field.Checkbox }
     */
    createEcSubmitCart: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'submitCart',
            fieldLabel: '{s name=field/submitCart}Submit cart{/s}',
            boxLabel: '{s name=field/submitCart/help}If this option is active, the cart will be submitted to PayPal for Express orders{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createEcButtonStyleColor: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'buttonStyleColor',
            fieldLabel: '{s name=field/ecButtonStyleColor}Button color{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleColor'),
            valueField: 'id',
            disabled: true
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createEcButtonStyleShape: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'buttonStyleShape',
            fieldLabel: '{s name=field/ecButtonStyleShape}Button shape{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleShape'),
            valueField: 'id',
            disabled: true
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createEcButtonStyleSize: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'buttonStyleSize',
            fieldLabel: '{s name=field/ecButtonStyleSize}Button size{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.EcButtonStyleSize'),
            valueField: 'id',
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
        me.ecSubmitCart.setDisabled(!checked);
        me.ecButtonStyleColor.setDisabled(!checked);
        me.ecButtonStyleShape.setDisabled(!checked);
        me.ecButtonStyleSize.setDisabled(!checked);
    }
});
// {/block}
