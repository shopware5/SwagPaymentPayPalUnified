import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';
const connection = MysqlFactory.getInstance();
const sweden = '25';
const sek = '5';

test.describe('Pay with trustly', () => {
    test.beforeAll(async () => {
        await clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Buy in sweden customer with sek', async ({ page }) => {
        await loginHelper.login(page);

        // Select SEK
        await page.locator('nav[role="menubar"] select[name="__currency"]').selectOption(sek);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Click text=Adresse ändern >> nth=0
        await page.locator('text=Adresse ändern').first().click();

        // Select sweden
        await page.locator('select[name="address\\[country\\]"]').selectOption(sweden);

        await Promise.all([
            page.waitForNavigation(/* { url: 'http://app_server/checkout/confirm' } */),
            page.locator('text=Adresse speichern').first().click()
        ]);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedTrustly
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        await page.click('input[name="sAGB"]');

        await page.waitForLoadState('load');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');

        await page.click('text=Success');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
