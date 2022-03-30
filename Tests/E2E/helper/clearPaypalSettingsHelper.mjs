import fs from 'fs';
import path from 'path';
const clearPaypalSettingsSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/truncate_paypal_tables.sql'), 'utf8');

export default clearPaypalSettingsSql;
