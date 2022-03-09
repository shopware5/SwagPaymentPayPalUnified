import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';

test.describe("Pay with invoice", () => {

    test('Buy a product with invoice', async ({ page }) => {

        //login
        await page.goto('/account');
        await page.waitForLoadState('load');
        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');
        await expect(page).toHaveURL(/.*account/);
        await expect(page.locator('.account--welcome > .panel--title')).toHaveText(/.*Mustermann.*/);

        //Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32');
        await page.click('.buybox--button');

        //Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        //Change payment
        await page.click('.btn--change-payment');
        await page.click('text=Kredit- oder Debitkarte');
        await page.click('text=Weiter >> nth=1');
        await page.click('input[name="sAGB"]');

        let locatorFieldNumber = await page.frameLocator('#braintree-hosted-field-number').locator('input[name="credit-card-number"]');
        await locatorFieldNumber.type(credentials.paypalCreditCard);

        let locatorExpiration = await page.frameLocator('#braintree-hosted-field-expirationDate').locator('input[name="expiration"]');
        await locatorExpiration.type('1130');

        let locatorCvv = await page.frameLocator('#braintree-hosted-field-cvv').locator('input[name="cvv"]');
        await locatorCvv.type('123');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    
    });

})
