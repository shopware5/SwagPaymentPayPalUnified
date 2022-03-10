import fs from 'fs';
import path from 'path';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';

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

    console.log('Truncating paypal settings');
    const truncatingSettings = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/truncate_paypal_tables.sql'), 'utf8');
    connection.query(truncatingSettings);

    console.log('Setting up default paypal settings');
    connection.query(defaultPaypalSettingsSql);

    console.log('Finished executing init Scripts');
}
