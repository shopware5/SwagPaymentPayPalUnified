import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
const connection = MysqlFactory.getInstance();

test.describe('Pay with credit card', () => {
    test.beforeAll(() => {
        clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Buy a product with credit card', async ({ page }) => {
        await loginHelper.login(page);

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

        await page.waitForLoadState('load');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Buy a product with credit card wich is secured', async ({ page }) => {
        await loginHelper.login(page);

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

        await page.frameLocator('#braintree-hosted-field-number').locator('#credit-card-number').type('5192507571573295');
        await page.frameLocator('#braintree-hosted-field-expirationDate').locator('#expiration').type('0530');
        await page.frameLocator('#braintree-hosted-field-cvv').locator('#cvv').type('123');

        await page.waitForLoadState('load');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');

        await expect(page.locator('.outlet')).toBeVisible();

        const loadingIndicator = await page.locator('.js--loading-indicator');
        const zIndexLoadingIndicator = await loadingIndicator.evaluate((element) =>
            window.getComputedStyle(element).getPropertyValue('z-index')
        );

        await expect(zIndexLoadingIndicator.match('990')).toBeTruthy();

        const overlay = await page.locator('.js--overlay.is--open');
        const zIndexOverlay = await overlay.evaluate((element) =>
            window.getComputedStyle(element).getPropertyValue('z-index')
        );

        await expect(zIndexOverlay.match('990')).toBeTruthy();
    });
});
