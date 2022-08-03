import getPaypalPaymentMethodIdSql from './getPaypalPaymentMethodId.mjs';
import MysqlFactory from './mysqlFactory.mjs';
const connection = MysqlFactory.getInstance();

export default (function () {
    return {
        getSelector: async function () {
            const payPayPaymentMethodIdPromise = function() {
                return new Promise((resolve, reject) => {
                    connection.query(getPaypalPaymentMethodIdSql, (err, result) => {
                        if (err) {
                            reject(err);
                        }

                        resolve(result);
                    });
                });
            };

            const result = await payPayPaymentMethodIdPromise();

            return '#payment_mean' + result[0].id;
        }
    };
}());
