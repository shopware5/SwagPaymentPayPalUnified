import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
const connection = MysqlFactory.getInstance();

test.describe('Pay with credit card', () => {
    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Buy a product with credit card', async ({ page }) => {
        // login
        await page.goto('/account');
        await page.waitForLoadState('load');
        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');
        await expect(page).toHaveURL(/.*account/);
        await expect(page.locator('h1[class="panel--title"]')).toHaveText(/.*Mustermann.*/);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32');
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        await page.click('text=Kredit- oder Debitkarte');
        await page.click('text=Weiter >> nth=1');

        await expect(page.locator('.payment--description')).toHaveText(/Kredit- oder Debitkarte/);
        await page.click('input[name="sAGB"]');

        await page.frameLocator('#braintree-hosted-field-number').locator('#credit-card-number').type(credentials.paypalCreditCard);
        await page.frameLocator('#braintree-hosted-field-expirationDate').locator('#expiration').type('1130');
        await page.frameLocator('#braintree-hosted-field-cvv').locator('#cvv').type('123');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
