import fs from 'fs';
import path from 'path';
import credentials from '../test/credentials.mjs';
let defaultPaypalSettingsSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/paypal_settings.sql'), 'utf8');
defaultPaypalSettingsSql = defaultPaypalSettingsSql.replace('sandbox_client_id::replace::me', credentials.paypalSandboxClientId);
defaultPaypalSettingsSql = defaultPaypalSettingsSql.replace('sandbox_client_secret::replace::me', credentials.paypalSandboxClientSecret);
defaultPaypalSettingsSql = defaultPaypalSettingsSql.replace('sandbox_paypal_payer_id::replace::me', credentials.paypalSandboxMerchantId);

export default defaultPaypalSettingsSql;
