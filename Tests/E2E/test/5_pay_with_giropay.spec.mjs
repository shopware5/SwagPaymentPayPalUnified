import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';

const germany = '2';

test.describe('Pay with Giropay', () => {
    test('Buy as german customer with euro', async ({ page }) => {
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

        // Click text=Adresse ändern >> nth=0
        await page.locator('text=Adresse ändern').first().click();

        await page.locator('select[name="address\\[country\\]"]').selectOption(germany);

        await Promise.all([
            page.waitForNavigation(/* { url: 'http://app_server/checkout/confirm' } */),
            page.locator('text=Adresse speichern').first().click()
        ]);

        // Change payment
        await page.click('.btn--change-payment');
        await page.click('text=Giropay');
        await page.click('text=Weiter >> nth=1');
        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await page.click('text=Success');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
