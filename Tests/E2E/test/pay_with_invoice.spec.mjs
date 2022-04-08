import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import setBirthdayAndPhone from '../helper/setBirthdayAndPhoneHelper.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';

const connection = MysqlFactory.getInstance();

test.use({ locale: 'de-DE' });

test.describe('Pay with invoice', () => {
    test.beforeAll(() => {
        clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
        connection.query(setBirthdayAndPhone);
    });

    test('Buy products with "Pay Upon Invoice"', async ({ page }) => {
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32');
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        await page.click('text=Rechnungskauf');
        await page.click('text=Weiter >> nth=1');

        // Check for the legalText
        await expect(page.locator('.swag-payment-paypal-unified-pay-upon-invoice-legal-text')).toHaveText(/Mit Klicken auf den Button akzeptieren Sie die/);

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")', { timeout: 120000 });

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);

        // Buy 10 products
        await page.goto('sommerwelten/172/sonnencreme-sunblocker-lsf-50');
        await page.locator('select[name="sQuantity"]').selectOption('10');
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        await page.click('text=Rechnungskauf');
        await page.click('text=Weiter >> nth=1');

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")', { timeout: 120000 });

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
