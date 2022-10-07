import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import tryUntilSucceed from '../helper/retryHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';
import loginHelper from '../helper/loginHelper.mjs';

const connection = MysqlFactory.getInstance();

test.use({ locale: 'de-DE' });

test.describe('Is SEPA fully functional', () => {
    test.skip();

    test.beforeAll(() => {
        clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Buy a product with SEPA', async ({ page }) => {
        page.on('frameattached', await function(frame) {
            frame.waitForLoadState('load');
        });

        await loginHelper.login(page);

        // Add product to cart
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to Checkout expect login the user
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedSepa
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        // buy the product with SEPA
        const locator = await page.frameLocator('.component-frame').locator('div[data-funding-source="sepa"]');
        await page.waitForLoadState('load');

        // check: can not check out without accept AGBs
        await locator.dispatchEvent('click');
        await expect(page.locator('label[for="sAGB"]')).toHaveClass('has--error');

        await page.click('input[name="sAGB"]');

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await paypalPage.route(/.*fundingSource=sepa.*/, route => {
            let url = route.request().url();
            url = url.replace(/buyerCountry=[A-Z]*/, '');
            url += '&buyerCountry=DE';

            route.continue({ url: url });
        });

        await paypalPage.locator('#bankIban').fill(credentials.sepaIban);
        await paypalPage.locator('#dateOfBirth').fill(credentials.sepaBirthday);
        await paypalPage.locator('#phone').fill(credentials.sepaPhone);
        await paypalPage.locator('text=Angaben speichern und PayPal-Konto eröffnen').click();

        await paypalPage.locator('button[type="submit"]').click();

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
