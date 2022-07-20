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

    console.log('Setting realistic data for customer');
    const setDefaultUserBirthday = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/set_customer_data.sql'), 'utf8');
    connection.query(setDefaultUserBirthday);

    console.log('Setting up apm fixtures');
    const apmFixtures = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/apm_fixtures.sql'), 'utf8');
    connection.query(apmFixtures.trim());

    console.log("Activate 'Buy in listing' setting");
    const buyInListingSetting = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/buy_in_listing.sql'), 'utf8');
    connection.query(buyInListingSetting.trim());

    console.log('Deactivate Cookie-Banner');
    const deactivateCookieBanner = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/deactivate_cookie_note.sql'), 'utf8');
    connection.query(deactivateCookieBanner.trim());

    console.log('Enable API access for demo admin');
    const enableApiAccess = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/api_access.sql'), 'utf8');
    connection.query(enableApiAccess.trim());

    console.log('Finished executing init Scripts');
}
