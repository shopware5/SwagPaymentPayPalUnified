//{block name="backend/paypal_unified/model/sale_transaction_fee"}
Ext.define('Shopware.apps.PaypalUnified.model.SaleTransactionFee', {

    /**
     * Extends the standard Ext Model
     * @type { String }
     */
    extend: 'Shopware.data.Model',

    /**
     * The fields used for this model
     * @type { Array }
     */
    fields: [
        //{block name="backend/paypal_unified/model/sale_transaction_fee/fields"}{/block}
        { name: 'value', type: 'float' },
        { name: 'currency', type: 'string' }
    ]
});
//{/block}