import { expect, test } from '@playwright/test';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import updatePlusSettingsSql from '../helper/updatePlusHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import fs from 'fs';
import path from 'path';
import credentials from './credentials.mjs';

const connection = MysqlFactory.getInstance();
const truncateTables = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/truncate_paypal_tables.sql'), 'utf8');

test.use({ viewport: { width: 1920, height: 1080 } });

test.describe('Backend testing: Check if plus popup is shown', () => {
    test.beforeEach(() => {
        connection.query(truncateTables);
        connection.query(defaultPaypalSettingsSql);
    });

    test('Deactivate plus on save', async ({ page }) => {
        connection.query(updatePlusSettingsSql);

        await page.goto('/backend');
        await expect(page).toHaveTitle(/Backend/);

        await page.fill('input[name="username"]', credentials.defaultBackendUserUsername);
        await page.fill('input[name="password"]', credentials.defaultBackendUserPassword);
        await page.click('#button-1019-btnEl');

        await page.waitForLoadState('load');

        await page.click('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');
        await page.click('text=Für diesen Shop aktivieren:Aktiviere diese Option, um PayPal für diesen Shop zu');

        await page.locator('text=Sandbox aktivieren:Aktiviere diese Option, wenn du die Integration testen möchte').scrollIntoViewIfNeeded();
        await page.locator('text=Sandbox aktivieren:Aktiviere diese Option, wenn du die Integration testen möchte').click();

        await page.locator('input[name="sandboxClientId"]').scrollIntoViewIfNeeded();
        await page.fill('input[name="sandboxClientId"]', credentials.paypalSandboxClientId);
        await page.fill('input[name="sandboxClientSecret"]', credentials.paypalSandboxClientSecret);
        await page.fill('input[name="sandboxPaypalPayerId"]', credentials.paypalSandboxMerchantId);

        await page.locator('button[role="button"]:has-text("PayPal Pay Upon Invoice Integration")').click();
        await page.locator('textarea[name="customerServiceInstructions"]').type('This field is required if PayUponInvoice is onboarded');
        await page.locator('button[role="button"]:has-text("Grundeinstellungen")').click();

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

        await page.locator('button[role="button"]:has-text("PayPal PLUS deaktivieren")').click();

        await page.click('text=Speichern');
    });
});
