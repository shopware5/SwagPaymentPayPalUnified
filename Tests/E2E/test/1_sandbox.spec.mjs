import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';

test.describe("Backend testing", () => {
    test('activate the sandbox', async ({ page }) => {
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
        await page.click('text=Für diesen Shop aktivieren:Aktiviere diese Option, um PayPal für diesen Shop zu  >> input[type="button"]')

        await page.click('text=Sandbox aktivieren:Aktiviere diese Option, wenn du die Integration testen möchte >> input[type="button"]');
        await page.fill('input[name="sandboxClientId"]', credentials.paypalSandboxClientId);
        await page.fill('input[name="sandboxClientSecret"]', credentials.paypalSandboxClientSecret);

        // Click button[role="button"]:has-text("PayPal Pay Upon Invoice Integration")
        await page.locator('button[role="button"]:has-text("PayPal Pay Upon Invoice Integration")').click();
        // Click textarea[name="customerServiceInstructions"]
        await page.locator('textarea[name="customerServiceInstructions"]').click();
        // Fill textarea[name="customerServiceInstructions"]
        await page.locator('textarea[name="customerServiceInstructions"]').fill('Fill');
        // Click button[role="button"]:has-text("Grundeinstellungen")
        await page.locator('button[role="button"]:has-text("Grundeinstellungen")').click();

        await Promise.all([
            page.click('text=Speichern'),
            page.waitForResponse(/.*PaypalUnifiedSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedExpressSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPlusSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedInstallmentsSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPayUponInvoiceSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedAdvancedCreditDebitCardSettings.*/),
        ]);

        await page.fill('input[name="sandboxPaypalPayerId"]', credentials.paypalSandboxMerchantId);

        await Promise.all([
            page.waitForResponse(/.*isCapable.*/),
            page.waitForResponse(/.*PaypalUnifiedSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedExpressSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPlusSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedInstallmentsSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedPayUponInvoiceSettings.*/),
            page.waitForResponse(/.*PaypalUnifiedAdvancedCreditDebitCardSettings.*/),
            page.click('text=Speichern'),
        ]);
    });
})

