// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/paypal_transactions/ShippingAddress"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.ShippingAddress', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.AbstractFieldset',
    alias: 'widget.paypal-unified-sidebarV2-order-fieldset-PaypalTransactions.ShippingAddress',
    title: '{s name="fieldset/ShippingAddress/title"}Shipping address{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        this.recipient = this.fieldFactory.createField('recipient');
        this.street = this.fieldFactory.createField('street');
        this.addressLineTwo = this.fieldFactory.createField('addressLineTwo');
        this.zipCode = this.fieldFactory.createField('zipCode');
        this.city = this.fieldFactory.createField('city');
        this.countryCode = this.fieldFactory.createField('countryCode');

        return [
            this.recipient,
            this.street,
            this.addressLineTwo,
            this.zipCode,
            this.city,
            this.countryCode,
        ];
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function(paypalOrderData) {
        var shipping = paypalOrderData.purchase_units[0].shipping,
            address = shipping.address;

        this.recipient.setValue(shipping.name.full_name);

        if (!Ext.isObject(address)) {
            return;
        }

        this.street.setValue(address.address_line_1);
        this.addressLineTwo.setValue(address.address_line_2);
        this.zipCode.setValue(address.postal_code);
        this.city.setValue(address.admin_area_2);
        this.countryCode.setValue(address.country_code);
    },
});
// {/block}
