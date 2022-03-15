import fs from 'fs';
import path from 'path';
import MysqlFactory from '../helper/mysqlFactory.mjs';

export default function () {
    console.log('Global Setup');
    const connection = MysqlFactory.getInstance();
    _importInitScripts(connection);
}

function _importInitScripts(connection) {
    console.log('Executing init Scripts');

    console.log('Setting birthday of default user');
    const setDefaultUserBirthday = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/set_default_user_birthday.sql'), 'utf8');
    connection.query(setDefaultUserBirthday);

    console.log('Setting up apm fixtures');
    const apmFixtures = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/apm_fixtures.sql'), 'utf8');
    connection.query(apmFixtures.trim());

    console.log("Activate 'Buy in listing' setting");
    const buyInListingSetting = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/buy_in_listing.sql'), 'utf8');
    connection.query(buyInListingSetting.trim());

    console.log('Finished executing init Scripts');
}
