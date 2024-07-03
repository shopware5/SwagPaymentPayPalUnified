import MysqlFactory from './mysqlFactory.mjs';
const connection = MysqlFactory.getInstance();

export default (function () {
    return {
        paymentMethodNames: {
            SwagPaymentPayPalUnified: 'SwagPaymentPayPalUnified',
            SwagPaymentPayPalUnifiedPayLater: 'SwagPaymentPayPalUnifiedPayLater',
            SwagPaymentPayPalUnifiedPayUponInvoice: 'SwagPaymentPayPalUnifiedPayUponInvoice',
            SwagPaymentPayPalUnifiedAdvancedCreditDebitCard: 'SwagPaymentPayPalUnifiedAdvancedCreditDebitCard',
            SwagPaymentPayPalUnifiedSepa: 'SwagPaymentPayPalUnifiedSepa',
            SwagPaymentPayPalUnifiedBancontact: 'SwagPaymentPayPalUnifiedBancontact',
            SwagPaymentPayPalUnifiedBlik: 'SwagPaymentPayPalUnifiedBlik',
            SwagPaymentPayPalUnifiedEps: 'SwagPaymentPayPalUnifiedEps',
            SwagPaymentPayPalUnifiedIdeal: 'SwagPaymentPayPalUnifiedIdeal',
            SwagPaymentPayPalUnifiedMultibanco: 'SwagPaymentPayPalUnifiedMultibanco',
            SwagPaymentPayPalUnifiedMyBank: 'SwagPaymentPayPalUnifiedMyBank',
            SwagPaymentPayPalUnifiedP24: 'SwagPaymentPayPalUnifiedP24',
            SwagPaymentPayPalUnifiedSofort: 'SwagPaymentPayPalUnifiedSofort',
            SwagPaymentPayPalUnifiedTrustly: 'SwagPaymentPayPalUnifiedTrustly'
        },

        /**
         * @param { string } paymentMethodName
         *
         * @returns { Promise<string> }
         */
        getSelector: async function (paymentMethodName) {
            const payPayPaymentMethodIdPromise = function(paymentMethodName) {
                return new Promise((resolve, reject) => {
                    const sql = 'SELECT id FROM s_core_paymentmeans WHERE `name` LIKE "%s";'.replace('%s', paymentMethodName);

                    connection.query(sql, (err, result) => {
                        if (err) {
                            reject(err);
                        }

                        resolve(result);
                    });
                });
            };

            const result = await payPayPaymentMethodIdPromise(paymentMethodName);

            return '#payment_mean' + result[0].id;
        }
    };
}());
