import { expect, test } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import backendLoginHelper from '../helper/backendLoginHelper.mjs';
import fs from 'fs';
import path from 'path';

const connection = MysqlFactory.getInstance();
const activatePlusSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/activate_plus_settings.sql'), 'utf8');
const truncateTables = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/truncate_paypal_tables.sql'), 'utf8');
const paypalSettings = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/paypal_settings.sql'), 'utf8');

test.use({ viewport: { width: 1920, height: 1080 } });

test.describe('Check pui plus notice', () => {
    test.beforeEach(() => {
        connection.query(truncateTables);
        connection.query(paypalSettings);
    });

    test('Check pui plus notice is shown', async ({ page }) => {
        connection.query(activatePlusSql);

        backendLoginHelper.login(page);

        await page.hover('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');

        await page.waitForLoadState('networkidle');

        await expect(page.locator('#plusPuiNotice > .block-message-inner > p')).toHaveText(/Kauf auf Rechnung wird als Teil von PayPal PLUS am 30.09.2022 eingestellt./);

        await Promise.all([
            page.waitForResponse(/.*isCapable.*/),
            page.waitForResponse(/.*PaypalUnifiedSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedExpressSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPlusSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedInstallmentsSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPayUponInvoiceSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedAdvancedCreditDebitCardSettings.*/),
            page.click('text=Speichern')
        ]);

        await page.waitForLoadState('networkidle');

        await expect(await page.$$('#plusPuiNotice')).toHaveLength(1);
    });

    test('Check pui plus notice is not shown', async ({ page }) => {
        backendLoginHelper.login(page);

        await page.hover('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');

        await page.waitForLoadState('networkidle');

        await expect(await page.$$('#plusPuiNotice')).toHaveLength(0);
    });
});
