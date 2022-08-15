import fs from 'fs';
import path from 'path';
const disableInContextMode = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/paypal_settings_use_not_in_context_mode.sql'), 'utf8');

export default disableInContextMode;
