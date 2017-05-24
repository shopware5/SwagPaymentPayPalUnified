// {namespace name="backend/paypal_unified_settings/tabs/installments"}
// {block name="backend/paypal_unified_settings/tabs/installment"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.tabs.Installments', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-settings-tabs-installments',
    title: '{s name=title}PayPal installments integration{/s}',

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

        me.installmentsActivate = me.createInstallmentsActivate();
        me.presentmentSelectionDetail = me.createPresentmentSelectionDetail();
        me.presentmentSelectionCart = me.createPresentmentSelectionCart();
        me.logoCheckBox = me.createLogoCheckBox();

        return [
            me.installmentsActivate,
            me.presentmentSelectionDetail,
            me.presentmentSelectionCart,
            me.logoCheckBox
        ];
    },

    createInstallmentsActivate: function() {
        var me = this;

        return Ext.create('Ext.form.field.Checkbox', {
            name: 'installmentsActive',
            fieldLabel: '{s name=field/activate}Activate PayPal installments{/s}',
            boxLabel: '{s name=field/activate/help}Activate to enable the PayPal installments integration for the selected shop.{/s}',
            inputValue: true,
            uncheckedValue: false,
            handler: Ext.bind(me.onActivateInstallments, me)
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createPresentmentSelectionDetail: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'installmentsPresentmentDetail',
            fieldLabel: '{s name=field/installmentsPresentmentDetail}Upstream-Presentment on detail page{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.InstallmentsPresentment'),
            helpText: '{s name=field/installmentsPresentmentDetail/help}Indicates which type of upstream-presentment should be displayed on the detail page.<br><br><u>None</u><br>Nothing will be displayed.<br><br><u>Simple</u><br>The customer gets a notification that the explains that installments is available for this cart. Details will be displayed if the customer clicks on the link.<br><br><u>Cheapest Rate</u><br>The cheapest rate will be displayed already. Attention! This option may influence the performance on the detail page!{/s}',
            valueField: 'id',
            value: 0,
            disabled: true
        });
    },

    /**
     * @returns { Ext.form.field.ComboBox }
     */
    createPresentmentSelectionCart: function() {
        return Ext.create('Ext.form.field.ComboBox', {
            name: 'installmentsPresentmentCart',
            fieldLabel: '{s name=field/installmentsPresentmentCart}Upstream-Presentment on cart page{/s}',
            store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.InstallmentsPresentment'),
            helpText: '{s name=field/installmentsPresentmentCart/help}Indicates which type of upstream-presentment should be displayed on the cart page.<br><br><u>None</u><br>Nothing will be displayed.<br><br><u>Simple</u><br>The customer gets a notification that the explains that installments is available for this cart. Details will be displayed if the customer clicks on the link.<br><br><u>Cheapest Rate</u><br>The cheapest rate will be displayed already. Attention! This option may influence the performance on the cart page!{/s}',
            valueField: 'id',
            value: 0,
            disabled: true
        });
    },

    createLogoCheckBox: function() {
        return Ext.create('Ext.form.field.Checkbox', {
            name: 'installmentsShowLogo',
            fieldLabel: '{s name=field/showLogo}Show logo in sidebar{/s}',
            helpText: '{s name=field/showLogo/help}If this option is active, a template will be included, which shows the logo of the PayPal installments integration in the sidebar element.{/s}',
            inputValue: true,
            uncheckedValue: false,
            disabled: true
        });
    },

    /**
     * @param { Shopware.apps.Base.view.element.Boolean } element
     * @param { Boolean } checked
     */
    onActivateInstallments: function(element, checked) {
        var me = this;

        me.presentmentSelectionDetail.setDisabled(!checked);
        me.presentmentSelectionCart.setDisabled(!checked);
        me.logoCheckBox.setDisabled(!checked);
    }
});
// {/block}
