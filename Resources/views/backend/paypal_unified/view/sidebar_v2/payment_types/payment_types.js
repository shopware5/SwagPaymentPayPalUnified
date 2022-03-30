// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/PaymentTypes/PaymentTypes"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.PaymentTypes.PaymentTypes', {
    authorization: {
        key: 'authorization',
        label: '{s name="paymentTypes/type/authorization"}Authorization{/s}'
    },

    capture: {
        key: 'capture',
        label: '{s name="paymentTypes/type/capture"}Capture{/s}'
    },

    refund: {
        key: 'refund',
        label: '{s name="paymentTypes/type/refund"}Refund{/s}'
    }
});
// {/block}
