// {block name="backend/paypal_unified/model/transaction_fee"}
Ext.define('Shopware.apps.PaypalUnified.model.TransactionFee', {

    /**
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        // {block name="backend/paypal_unified/model/transaction_fee/fields"}{/block}
        { name: 'value', type: 'float' },
        { name: 'currency', type: 'string' }
    ]
});
// {/block}
