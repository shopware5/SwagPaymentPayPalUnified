import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';
import payLaterSettingsHelper from '../helper/payLaterSettingsHelper.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Test Pay Later is shown', () => {
    test.beforeEach(async() => {
        await connection.query(defaultPaypalSettingsSql);
        await payLaterSettingsHelper.activateAll();
        await clearCacheHelper.clearCache();
    });

    test('Is PayLater button available: ProductDetailPage, OffCanvasBasket, CheckoutPage, ProductListingPage @notIn5.2', async({ page }) => {
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
});
