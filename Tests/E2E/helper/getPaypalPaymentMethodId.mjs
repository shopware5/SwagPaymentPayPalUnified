import fs from 'fs';
import path from 'path';
const getPaypalPaymentMethodIdSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/get_paypal_payment_method_id.sql'), 'utf8');

export default getPaypalPaymentMethodIdSql;
