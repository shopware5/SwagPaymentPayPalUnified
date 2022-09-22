import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import credentials from './credentials.mjs';
import leadingZeroProductSql from '../helper/updateProductNumberAddLeadingZero.mjs';
import tryUntilSucceed from '../helper/retryHelper.mjs';
import offCanvasSettingHelper from '../helper/offCanvasSettingHelper.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Is Express Checkout button available', () => {
    test.beforeAll(() => {
        clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Check product cart modal @notIn5.2', async ({ page }) => {
        await offCanvasSettingHelper.deactivateOffCanvasCart();
        await clearCacheHelper.clearCache();

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        await page.locator('text=In den Warenkorb').click();
        await page.waitForLoadState('load');

        const locator = await page.frameLocator('.js--modal .component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await offCanvasSettingHelper.activateOffCanvas();
        await clearCacheHelper.clearCache();
    });

    test('Check product detail page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await paypalPage.route(/.*checkoutnow.*/, route => {
            let url = route.request().url();
            url = url.replace(/buyerCountry=[A-Z]*/, '');
            url += '&buyerCountry=DE';

            route.continue({ url: url });
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);
        await paypalPage.locator('button:has-text("Weiter")').click();
        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);
        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Check offcanvas cart', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        await page.locator('text=In den Warenkorb').click();
        await page.waitForLoadState('load');
        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);

        const locator = await page.frameLocator('.ajax--cart >> .component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await paypalPage.route(/.*checkoutnow.*/, route => {
            let url = route.request().url();
            url = url.replace(/buyerCountry=[A-Z]*/, '');
            url += '&buyerCountry=DE';

            route.continue({ url: url });
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);
        await paypalPage.locator('button:has-text("Weiter")').click();
        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);
        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Check checkout cart page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        await page.locator('text=In den Warenkorb').click();
        await page.waitForLoadState('load');

        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);

        await page.locator('text=Warenkorb bearbeiten').click();
        await page.waitForLoadState('load');

        await expect(page).toHaveURL('/checkout/cart');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await paypalPage.route(/.*checkoutnow.*/, route => {
            let url = route.request().url();
            url = url.replace(/buyerCountry=[A-Z]*/, '');
            url += '&buyerCountry=DE';

            route.continue({ url: url });
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);
        await paypalPage.locator('button:has-text("Weiter")').click();
        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);
        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Check register page', async ({ page }) => {
        page.on('frameattached', await function (frame) {
            frame.waitForLoadState('load');
        });

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        await page.locator('text=In den Warenkorb').click();
        await page.waitForLoadState('load');

        await expect(page.locator('.ajax--cart')).toHaveText(/Zur Kasse/);
        await page.locator('text=Zur Kasse').click();
        await page.waitForLoadState('load');

        await expect(page).toHaveURL('/checkout/confirm');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await paypalPage.route(/.*checkoutnow.*/, route => {
            let url = route.request().url();
            url = url.replace(/buyerCountry=[A-Z]*/, '');
            url += '&buyerCountry=DE';

            route.continue({ url: url });
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);
        await paypalPage.locator('button:has-text("Weiter")').click();
        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);
        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Check product listing page @notIn5.2', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/', { waitUntil: 'load' });

        const locator = await page.frameLocator('.component-frame >> nth=1').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await paypalPage.route(/.*checkoutnow.*/, route => {
            let url = route.request().url();
            url = url.replace(/buyerCountry=[A-Z]*/, '');
            url += '&buyerCountry=DE';

            route.continue({ url: url });
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);
        await paypalPage.locator('button:has-text("Weiter")').click();
        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);
        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await page.click('input[name="sAGB"]');

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Test if product with order number with leading zero is buy able', async ({ page }) => {
        connection.query(leadingZeroProductSql.setProductNumberWithLeadingZero());

        await page.goto('/genusswelten/koestlichkeiten/272/spachtelmasse', { waitUntil: 'load' });

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        await page.waitForTimeout(7000);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        connection.query(leadingZeroProductSql.reset());
    });
});
