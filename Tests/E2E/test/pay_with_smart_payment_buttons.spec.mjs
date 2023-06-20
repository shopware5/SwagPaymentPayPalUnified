import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import tryUntilSucceed from '../helper/retryHelper.mjs';
import useSmartPaymentButtonsSql from '../helper/useSmartPaymentButtons.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Is SPB fully functional', () => {
    test.beforeEach(async() => {
        await connection.query(defaultPaypalSettingsSql);
        await connection.query(useSmartPaymentButtonsSql);
        await clearCacheHelper.clearCache();
    });

    test('Buy a product with PayPal using Smart Payment Buttons', async({ page }) => {
        // Add product to cart
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to Checkout expect login the user
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnified
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');

        await page.click('text=Weiter >> nth=1');

        const paypalIframe = await page.frameLocator('.component-frame');

        // buy the product with PayPal
        const locator = paypalIframe.locator('div[data-funding-source="paypal"]');
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

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);

        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);

        await paypalPage.locator('#btnLogin').click();

        await paypalPage.locator('button:has-text("Kauf abschließen"), button:has-text("Jetzt zahlen")').click();

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
