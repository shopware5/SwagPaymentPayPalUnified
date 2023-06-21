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
        let fistPayLaterButtonContainer = await page.locator('div[data-showpaylater="1"]').first();
        await expect(fistPayLaterButtonContainer).toBeVisible();

        // Go to detail page
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        // Check product detail page
        await expect(page.locator('div[data-showpaylater="1"]')).toBeVisible();

        // Add product to cart
        await page.click('.buybox--button');

        // Check offcanvas basket
        await expect(page.locator('.ajax--cart').locator('div[data-showpaylater="1"]')).toBeVisible();

        // Go to checkout
        await page.goto('checkout/confirm', { waitUntil: 'load' });
        // Check checkout page
        await expect(page.locator('div[data-showpaylater="1"]')).toBeVisible();

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
        await expect(page.locator('div[data-showpaylater="1"]')).toBeVisible();
    });
});
