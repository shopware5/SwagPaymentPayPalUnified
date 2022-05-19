import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import credentials from './credentials.mjs';
import leadingZeroProductSql from '../helper/updateProductNumberAddLeadingZero.mjs';
import tryUntilSucceed from '../helper/retryHelper.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Is Express Checkout button available', () => {
    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Check product detail page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');
        await page.waitForLoadState('load');

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
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

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
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);
        await page.locator('text=Warenkorb bearbeiten').click();
        await expect(page).toHaveURL('/checkout/cart');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');

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

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Zur Kasse/);
        await page.locator('text=Zur Kasse').click();
        await expect(page).toHaveURL('/checkout/confirm');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');

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
        await page.goto('/sommerwelten/beachwear/');

        const locator = await page.frameLocator('.component-frame >> nth=1').locator('.paypal-button');

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

    test('Check product cart modal @notIn5.2', async ({ page }) => {
        await page.goto('/backend');
        await expect(page).toHaveTitle(/Backend/);

        await page.fill('input[name="username"]', credentials.defaultBackendUserUsername);
        await page.fill('input[name="password"]', credentials.defaultBackendUserPassword);
        await page.click('#button-1019-btnEl');

        await page.waitForLoadState('load');

        await page.click('.settings--main');
        await page.click('.settings--theme-manager');

        await page.waitForLoadState('load');

        await page.click('.thumbnail .enabled');
        await page.locator('button[role="button"]:has-text("Theme konfigurieren")').click();

        await page.waitForLoadState('load');

        /** Tab -> Konfiguration */
        await page.click('//html/body/div[6]/div[5]/div[2]/div/div/div/div/div/div[1]/div[1]/div[2]/div/div[2]/em/button/span[1]');

        await page.waitForLoadState('load');

        /** Wenn aktiv, wird der Offcanvas Warenkorb verwendet. */
        await page.click('//html/body/div[6]/div[5]/div[2]/div/div/div/div/div/div[2]/div[2]/div/fieldset[1]/div/table[1]/tbody/tr/td[2]/input');

        await page.click('text=Speichern');

        await page.waitForLoadState('load');

        await page.click('text=Themes kompilieren');

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        const locator = await page.frameLocator('.js--modal >> .component-frame').locator('.paypal-button');

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);

        await page.goto('/backend');
        await expect(page).toHaveTitle(/Backend/);

        await page.fill('input[name="username"]', credentials.defaultBackendUserUsername);
        await page.fill('input[name="password"]', credentials.defaultBackendUserPassword);
        await page.click('#button-1019-btnEl');

        await page.waitForLoadState('load');

        await page.click('.settings--main');
        await page.click('.settings--theme-manager');

        await page.waitForLoadState('load');

        await page.click('.thumbnail .enabled');
        await page.locator('button[role="button"]:has-text("Theme konfigurieren")').click();

        await page.waitForLoadState('load');

        /** Tab -> Konfiguration */
        await page.click('//html/body/div[6]/div[5]/div[2]/div/div/div/div/div/div[1]/div[1]/div[2]/div/div[2]/em/button/span[1]');

        await page.waitForLoadState('load');

        /** Wenn aktiv, wird der Offcanvas Warenkorb verwendet. */
        await page.click('//html/body/div[6]/div[5]/div[2]/div/div/div/div/div/div[2]/div[2]/div/fieldset[1]/div/table[1]/tbody/tr/td[2]/input');

        await page.click('text=Speichern');

        await page.waitForLoadState('load');

        await page.click('text=Themes kompilieren');
    });

    test('Test if product with order number with leading zero is buy able', async ({ page }) => {
        connection.query(leadingZeroProductSql.setProductNumberWithLeadingZero());

        await page.goto('/genusswelten/koestlichkeiten/272/spachtelmasse');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button');
        await page.waitForLoadState('load');

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
