import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';

const connection = MysqlFactory.getInstance();

test.use({ locale: 'de-DE' });

test.describe('Pay with invoice', () => {
    test.beforeAll(async () => {
        await clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Buy one product with "Pay Upon Invoice"', async ({ page }) => {
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const paymentMethodSelector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedPayUponInvoice
        );
        await page.locator(paymentMethodSelector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        // Check for the legalText
        await expect(page.locator('.swag-payment-paypal-unified-pay-upon-invoice-legal-text')).toHaveText(/Mit Klicken auf den Button akzeptieren Sie die/);

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")', { timeout: 120000 });

        await expect(page.locator('.teaser--title').first()).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);

        await expect(page.locator('.unified--instruction')).toHaveText(/.*Bitte überweisen Sie.*/);
    });

    test('Buy ten products with "Pay Upon Invoice"', async ({ page }) => {
        await loginHelper.login(page);

        await page.goto('sommerwelten/172/sonnencreme-sunblocker-lsf-50', { waitUntil: 'load' });
        await page.locator('select[name="sQuantity"]').selectOption('10');
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedPayUponInvoice
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")', { timeout: 120000 });

        await expect(page.locator('.teaser--title').first()).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);

        await expect(page.locator('.unified--instruction')).toHaveText(/.*Bitte überweisen Sie.*/);
    });
});
