// {namespace name="backend/paypal_unified/overview/extensions/filter"}
// {block name="backend/paypal_unified/overview/extensions/filter"}
Ext.define('Shopware.apps.PaypalUnified.view.overview.extensions.Filter', {
    extend: 'Shopware.listing.FilterPanel',
    alias: 'widget.paypal-unified-overview-filter-panel',

    configure: function () {
        var paymentStatusStore = Ext.create('Shopware.apps.Base.store.OrderStatus'),
            orderStatusStore = Ext.create('Shopware.apps.Base.store.PaymentStatus');

        return {
            controller: 'PaypalUnified',
            model: 'Shopware.apps.PaypalUnified.model.ShopwareOrder',
            fields: {
                cleared: {
                    xtype: 'combobox',
                    fieldLabel: '{s name="filter/paymentstatus"}{/s}',
                    displayField: 'description',
                    valueField: 'id',
                    store: orderStatusStore,
                },
                status: {
                    xtype: 'combobox',
                    fieldLabel: '{s name="filter/orderstatus"}{/s}',
                    displayField: 'description',
                    valueField: 'id',
                    store: paymentStatusStore,
                }
            }
        };
    },
});
// {/block}