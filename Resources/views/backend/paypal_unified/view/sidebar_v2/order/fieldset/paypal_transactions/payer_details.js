// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/order/fieldset/paypal_transactions/PayerDetails"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paypalTransactions.PayerDetails', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.AbstractFieldset',
    alias: 'widget.paypal-unified-sidebarV2-order-fieldset-PaypalTransactions.PayerDetails',
    title: '{s name="fieldset/PayerDetails/title"}Customer{/s}',

    /**
     * @return { Array }
     */
    createItems: function() {
        this.customerId = this.fieldFactory.createField('customerId');
        this.customerEmail = this.fieldFactory.createField('customerEmail');
        this.givenName = this.fieldFactory.createField('givenName');
        this.surname = this.fieldFactory.createField('surname');
        this.phone = this.fieldFactory.createField('phone');
        this.countryCode = this.fieldFactory.createField('countryCode');

        return [
            this.customerId,
            this.customerEmail,
            this.givenName,
            this.surname,
            this.phone,
            this.countryCode,
        ];
    },

    /**
     * @param { Object } paypalOrderData
     */
    setOrderData: function (paypalOrderData) {
        if (paypalOrderData.payer === null) {
            return;
        }

        this.customerId.setValue(paypalOrderData.payer.payer_id);
        this.customerEmail.setValue(paypalOrderData.payer.email_address);
        this.givenName.setValue(paypalOrderData.payer.name.given_name);
        this.surname.setValue(paypalOrderData.payer.name.surname);
        this.phone.setValue(paypalOrderData.payer.phone.phone_number.national_number);
        this.countryCode.setValue(paypalOrderData.payer.address.country_code);
    },
});
// {/block}
