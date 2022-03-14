import fs from 'fs';
import path from 'path';
const updatePlusSettingsSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/activate_plus_settings.sql'), 'utf8');

export default updatePlusSettingsSql;
