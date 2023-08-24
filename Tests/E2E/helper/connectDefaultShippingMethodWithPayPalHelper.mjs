import MysqlFactory from './mysqlFactory.mjs';
import fs from 'fs';
import path from 'path';

const connectDefaultShippingMethodWithPayPalSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/connect_default_shipping_method_with_paypal.sql'), 'utf8');
const connection = MysqlFactory.getInstance();

export default (function() {
    return {
        connectDefaultShippingMethodWithPayPal: async function() {
            await new Promise((resolve, reject) => {
                connection.query(connectDefaultShippingMethodWithPayPalSql, function(err) {
                    if (err) {
                        reject(err);
                    }
                    resolve();
                });
            });
        }
    };
}());
