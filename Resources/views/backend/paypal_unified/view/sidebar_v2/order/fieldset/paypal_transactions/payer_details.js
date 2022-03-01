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
    setOrderData: function(paypalOrderData) {
        if (paypalOrderData.payer === null) {
            return;
        }

        if (paypalOrderData.payer.hasOwnProperty('payer_id')) {
            this.customerId.setValue(paypalOrderData.payer.payer_id)
        }

        if (paypalOrderData.payer.hasOwnProperty('email_address')) {
            this.customerEmail.setValue(paypalOrderData.payer.email_address);
        }

        if (paypalOrderData.payer.name !== null &&
            paypalOrderData.payer.name.hasOwnProperty('given_name') &&
            paypalOrderData.payer.name.hasOwnProperty('surname')
        ) {
            this.givenName.setValue(paypalOrderData.payer.name.given_name);
            this.surname.setValue(paypalOrderData.payer.name.surname);
        }

        if (paypalOrderData.payer.phone !== null &&
            paypalOrderData.payer.phone.hasOwnProperty('phone_number') &&
            paypalOrderData.payer.phone.phone_number !== null &&
            paypalOrderData.payer.phone.phone_number.hasOwnProperty('national_number')
        ) {
            this.phone.setValue(paypalOrderData.payer.phone.phone_number.national_number);
        }

        if (paypalOrderData.payer.address !== null &&
            paypalOrderData.payer.address.hasOwnProperty('country_code')) {
            this.countryCode.setValue(paypalOrderData.payer.address.country_code);
        }
    },
});
// {/block}
