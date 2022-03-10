import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';

test.describe('Frontend', () => {
    test('Buy a product with paypal', async ({ page }) => {
        // login
        await page.goto('/account');
        await page.waitForLoadState('load');
        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');
        await expect(page).toHaveURL(/.*account/);
        await expect(page.locator('.account--welcome > .panel--title')).toHaveText(/.*Mustermann.*/);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32');
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        await page.click('text=PayPal Bezahlung per PayPal - einfach, schnell und sicher. >> input[name="payment"]');
        await page.click('text=Weiter >> nth=1');
        await page.click('input[name="sAGB"]');

        const locator = await page.frameLocator('.component-frame').locator('div[role="button"]:has-text("Jetzt kaufen")');
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);

        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);

        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await expect(page.locator('.alert.is--success')).toHaveText(/Ihre Zahlung wurde erstellt\. Bitte schließen Sie sie ab, indem Sie Ihre Bestellung bestätigen\./);

        await page.click('input[name="sAGB"]');
        await page.click('button[form="confirm--form"].is--primary');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
