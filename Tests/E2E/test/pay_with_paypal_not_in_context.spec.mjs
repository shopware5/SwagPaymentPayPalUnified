import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import disableInContextMode from '../helper/disableInContextMode.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';
const connection = MysqlFactory.getInstance();

test.describe('Disabled inContext mode', () => {
    test.beforeAll(() => {
        clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Disabled inContext mode and buy a product with paypal', async ({ page }) => {
        // Disable InContext mode
        connection.query(disableInContextMode);

        // Login
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnified
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        await page.waitForLoadState('load');

        await page.click('input[name="sAGB"]');

        // Go to PayPal checkout
        await page.click('text=Zahlungspflichtig bestellen');

        await page.locator('#email').fill(credentials.paypalCustomerEmail);
        await page.locator('#password').fill(credentials.paypalCustomerPassword);
        await page.locator('#btnLogin').click();
        await page.locator('button:has-text("Jetzt zahlen")').click();

        await page.waitForLoadState('load');

        // Checkout Finished
        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
