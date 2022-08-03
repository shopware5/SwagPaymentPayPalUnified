import fs from 'fs';
import path from 'path';
const useSmartPaymentButtonsSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/paypal_settings_use_smart_payment_buttons.sql'), 'utf8');

export default useSmartPaymentButtonsSql;
