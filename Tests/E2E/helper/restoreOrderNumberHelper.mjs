import MysqlFactory from './mysqlFactory.mjs';

const connection = MysqlFactory.getInstance();

export default (function() {
    return {
        getRestoredOrderNumber: async function() {
            var orderNumbers;

            await new Promise((resolve, reject) => {
                connection.query('SELECT `order_number` FROM `swag_payment_paypal_unified_order_number_pool`', function(err, result) {
                    if (err) {
                        reject(err);
                    }

                    orderNumbers = result;
                    resolve();
                });
            });

            return orderNumbers;
        }
    };
}());
