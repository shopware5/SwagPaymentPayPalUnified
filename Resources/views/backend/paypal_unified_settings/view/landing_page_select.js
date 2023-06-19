// {namespace name="backend/paypal_unified_settings/landing_page_select"}
// {block name="backend/paypal_unified_settings/landing_page_select"}
Ext.define('Shopware.apps.PaypalUnifiedSettings.view.LandingPageSelect', {
    extend: 'Ext.form.field.ComboBox',

    name: 'landingPageType',
    editable: false,
    fieldLabel: '{s name="fieldLabel"}PayPal landing page{/s}',
    helpText: '{s name="helpText"}<u>Login</u><br>The PayPal site displays a login screen as landing page.<br><br><u>Registration</u><br>The PayPal site displays a registration form as landing page.{/s}',
    queryMode: 'local',
    valueField: 'key',
    displayField: 'label',
    value: 'NO_PREFERENCE',

    listConfig: {
        getInnerTpl: function() {
            return [
                '{literal}',
                '<div class="layout-info">',
                '<h1>{label}</h1>',
                '<div>{description}</div>',
                '</div>',
                '<div class="x-clear" />',
                '{/literal}'
            ].join('');
        }
    },

    store: Ext.create('Shopware.apps.PaypalUnifiedSettings.store.LandingPageType')
});
// {/block}
