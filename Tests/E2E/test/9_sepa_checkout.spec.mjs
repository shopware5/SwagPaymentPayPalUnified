import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';

test.describe('Is SEPA fully functional', () => {
    test('Buy a product with SEPA', async ({ page }) => {
        // Add product to cart
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');
        await page.click('.buybox--button');

        // Go to Checkout expect login the user
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment to SEPA
        await page.click('.btn--change-payment');
        await page.click('text=Lastschrift');
        await page.click('text=Weiter >> nth=1');

        // buy the product with SEPA
        const locator = await page.frameLocator('.component-frame').locator('div[data-funding-source="sepa"]');
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await paypalPage.locator('#bankIban').fill(credentials.sepaIban);
        await paypalPage.locator('#dateOfBirth').fill(credentials.sepaBirthday);
        await paypalPage.locator('#phone').fill(credentials.sepaPhone);

        await paypalPage.locator('label[for="onboardOptionGuest"]').click();
        await paypalPage.locator('label[for="sepaMandate"]').click();

        await paypalPage.locator('button[type="submit"]').click();

        await expect(page.locator('.alert.is--success')).toHaveText(/Ihre Zahlung wurde erstellt\. Bitte schließen Sie sie ab, indem Sie Ihre Bestellung bestätigen\./);

        await page.click('input[name="sAGB"]');
        await page.click('button[form="confirm--form"].is--primary');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
