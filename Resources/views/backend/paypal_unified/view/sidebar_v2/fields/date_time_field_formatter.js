// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/fields/dateTimeFieldFormatter"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.fields.DateTimeFieldFormatter', {

    /**
     * @param { String } value
     *
     * @return { String }
     */
    format: function(value) {
        if (!value) {
            return '';
        }

        var date = new Date(value);

        return Ext.Date.format(date, 'd.m.Y H:i');
    },
});
// {/block}
