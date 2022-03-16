import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import fs from 'fs';
import path from 'path';
const connection = MysqlFactory.getInstance();
const truncateTables = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/truncate_paypal_tables.sql'), 'utf8');

test.use({ viewport: { width: 1920, height: 1080 } });

test.describe('Backend testing', () => {
    test.beforeEach(() => {
        connection.query(truncateTables);
    });

    test('activate the sandbox', async ({ page }) => {
        await page.goto('/backend');
        await expect(page).toHaveTitle(/Backend/);
        await page.fill('input[name="username"]', credentials.defaultBackendUserUsername);
        await page.fill('input[name="password"]', credentials.defaultBackendUserPassword);
        await page.click('#button-1019-btnEl');
        await page.waitForLoadState('networkidle');

        await page.click('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');
        await page.click('text=Für diesen Shop aktivieren:Aktiviere diese Option, um PayPal für diesen Shop zu  >> input[type="button"]');

        await page.locator('text=Sandbox aktivieren:Aktiviere diese Option, wenn du die Integration testen möchte >> input[type="button"]').scrollIntoViewIfNeeded();
        await page.locator('text=Sandbox aktivieren:Aktiviere diese Option, wenn du die Integration testen möchte >> input[type="button"]').click();

        await page.locator('input[name="sandboxClientId"]').scrollIntoViewIfNeeded();
        await page.fill('input[name="sandboxClientId"]', credentials.paypalSandboxClientId);
        await page.fill('input[name="sandboxClientSecret"]', credentials.paypalSandboxClientSecret);

        await page.locator('button[role="button"]:has-text("PayPal Express Checkout Integration")').click();
        await page.locator('text=\'Direkt zu PayPal\' auf Listing-Seiten:Wenn diese Option aktiv ist, wird der Expr >> input[type="button"]').click();

        await Promise.all([
            page.click('text=Speichern'),
            page.waitForResponse(/.*PaypalUnifiedSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedExpressSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPlusSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedInstallmentsSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPayUponInvoiceSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedAdvancedCreditDebitCardSettings.*/)
        ]);

        await page.locator('button[role="button"]:has-text("Grundeinstellungen")').click();
        await page.fill('input[name="sandboxPaypalPayerId"]', credentials.paypalSandboxMerchantId);

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

        // Fill textarea[name="customerServiceInstructions"]
        await page.locator('button[role="button"]:has-text("Grundeinstellungen")').click();
        await page.locator('textarea[name="customerServiceInstructions"]').type('This field is required if PayUponInvoice is onboarded');

        await page.click('text=Speichern');
    });
});
