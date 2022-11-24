import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import tryUntilSucceed from '../helper/retryHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';
import payLaterSettingsHelper from '../helper/payLaterSettingsHelper.mjs';

const connection = MysqlFactory.getInstance();

test.use({ locale: 'de-DE' });

test.describe('Is Pay Later fully functional', () => {
    test.beforeAll(async () => {
        await clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Is PayLater button available: ProductDetailPage, OffCanvasBasket, CheckoutPage, ProductListingPage @notIn5.2', async ({ page }) => {
        await payLaterSettingsHelper.activateAll();
        await clearCacheHelper.clearCache();

        // Go to product listing
        await page.goto('/sommerwelten/beachwear/', { waitUntil: 'load' });

        // Check listing page
        const listingPageLocator = await page.frameLocator('.component-frame').first().locator('div[data-funding-source="paylater"]');
        await expect(listingPageLocator.locator('.paypal-button-text')).toHaveText(/Später Bezahlen/);

        // Go to detail page
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        // Check product detail page
        const detailPageLocator = await page.frameLocator('.component-frame').locator('div[data-funding-source="paylater"]');
        await expect(detailPageLocator.locator('.paypal-button-text')).toHaveText(/Später Bezahlen/);

        // Add product to cart
        await page.click('.buybox--button');

        // Check offcanvas basket
        const offCanvasLocator = await page.locator('.ajax--cart').frameLocator('.component-frame').locator('div[data-funding-source="paylater"]');
        await expect(offCanvasLocator.locator('.paypal-button-text')).toHaveText(/Später Bezahlen/);

        // Go to checkout
        await page.goto('checkout/confirm', { waitUntil: 'load' });

        // Check checkout page
        const checkoutLocator = await page.frameLocator('.component-frame').locator('div[data-funding-source="paylater"]');
        await expect(checkoutLocator.locator('.paypal-button-text')).toHaveText(/Später Bezahlen/);

        // Login
        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnified
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        // Check checkout confirm page
        const checkoutConfirmLocator = await page.frameLocator('.component-frame').locator('div[data-funding-source="paylater"]');
        await expect(checkoutConfirmLocator.locator('.paypal-button-text')).toHaveText(/Später Bezahlen/);
    });

    test('Buy a product with Pay Later', async ({ page }) => {
        test.skip();
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
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedPayLater
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        // buy the product with Pay later
        const locator = await page.frameLocator('.component-frame').locator('div[data-funding-source="paylater"]');
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

        await paypalPage.locator('label[for="credit-offer-1"]').click();

        await paypalPage.locator('#payment-submit-btn').click();

        await paypalPage.locator('#payment-submit-btn').click();

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
