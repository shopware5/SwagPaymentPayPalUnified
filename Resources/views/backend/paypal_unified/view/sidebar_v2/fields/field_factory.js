// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/fields/fieldFactory"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.fields.FieldFactory', {

    fieldLabels: {
        totalAmount: '{s name="paypalUnified/V2/totalAmount"}Total amount{/s}',
        subtotal: '{s name="field/factory/subTotal"}Subtotal{/s}',
        shippingCoasts: '{s name="field/factory/shippingCosts"}Shipping costs{/s}',

        orderId: '{s name="field/factory/orderId"}Order ID{/s}',
        intent: '{s name="field/factory/intent"}Intent{/s}',
        status: '{s name="paypalUnified/V2/status"}Status{/s}',
        createTime: '{s name="paypalUnified/V2/createTime"}Created{/s}',
        updateTime: '{s name="paypalUnified/V2/updateTime"}Updated{/s}',

        customerId: '{s name="field/factory/customerId"}Customer ID{/s}',
        customerEmail: '{s name="field/factory/customerEmail"}Email{/s}',
        givenName: '{s name="field/factory/givenName"}First name{/s}',
        surname: '{s name="field/factory/surname"}Last name{/s}',
        phone: '{s name="field/factory/phone"}Phone{/s}',
        countryCode: '{s name="field/factory/countryCode"}Country code{/s}',

        recipient: '{s name="field/factory/recipient"}Recipient{/s}',
        street: '{s name="field/factory/street"}Street{/s}',
        addressLineTwo: '{s name="field/factory/addressLineTwo"}Address line 2{/s}',
        city: '{s name="field/factory/city"}City{/s}',
        zipCode: '{s name="field/factory/zipCode"}Zipcode{/s}',

        paymentType: '{s name="field/factory/paymentType"}Payment type{/s}',
        paymentStatus: '{s name="field/factory/paymentStatus"}Payment status{/s}',
        paymentId: '{s name="field/factory/paymentId"}Payment ID{/s}',
        paymentCustomId: '{s name="field/factory/paymentCustomId"}Custom ID{/s}',
        paymentAmount: '{s name="field/factory/paymentAmount"}Payment amount{/s}',
        paymentCurrency: '{s name="field/factory/paymentCurrency"}Payment currency{/s}',
        paymentCreated: '{s name="paypalUnified/V2/createTime"}Created{/s}',
        paymentUpdated: '{s name="paypalUnified/V2/updateTime"}Updated{/s}',
        paymentExpirationTime: '{s name="paypalUnified/V2/expireTime"}Expiration time{/s}',
    },

    /**
     * @param { string } fieldName
     *
     * @return { Ext.form.field.Text }
     */
    createField: function(fieldName) {
        var config = {
            name: fieldName,
            fieldLabel: this.getFieldLabel(fieldName),
            readOnly: true,
        };

        return Ext.create('Ext.form.field.Text', config);
    },

    /**
     * @param { string } fieldName
     *
     * @return { string }
     */
    getFieldLabel: function(fieldName) {
        if (!this.fieldLabels.hasOwnProperty(fieldName)) {
            return ''
        }

        return this.fieldLabels[fieldName];
    },
});
// {/block}
