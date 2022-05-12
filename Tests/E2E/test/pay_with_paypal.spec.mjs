import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import loginHelper from '../helper/loginHelper.mjs';
const connection = MysqlFactory.getInstance();

test.describe('Frontend', () => {
    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Buy a product with paypal', async ({ page }) => {
        page.on('frameattached', await function (frame) {
            frame.waitForLoadState('load');
        });

        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32');
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        await page.click('label:has-text("PayPal")');
        await page.click('text=Weiter >> nth=1');
        await page.click('input[name="sAGB"]');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button:has-text("Jetzt kaufen")');
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);

        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);

        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Jetzt zahlen")').click();

        await expect(page.locator('.alert.is--success')).toHaveText(/Ihre Zahlung wurde erstellt\. Bitte schließen Sie sie ab, indem Sie Ihre Bestellung bestätigen\./);

        await page.click('input[name="sAGB"]');
        await page.click('button[form="confirm--form"].is--primary');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
});
