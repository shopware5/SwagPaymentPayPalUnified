import fs from 'fs';
import path from 'path';

const setBirthdayAndPhoneSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/set_birthday_and_phone.sql'), 'utf8');

export default setBirthdayAndPhoneSql;
