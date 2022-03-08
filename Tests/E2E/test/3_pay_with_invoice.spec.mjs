import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import fs from "fs";
import path from "path";
const connection = MysqlFactory.getInstance();
const resetUserSettings = fs.readFileSync(path.join(path.resolve(''),'setup/sql/reset_user_settings.sql'), 'utf8');

test.describe("Pay with invoice", () => {
    test.beforeEach(() => {
        connection.query(resetUserSettings);
    })

    test('Buy a product with invoice', async ({ page }) => {
        //login
        await page.goto('/account');
        await page.waitForLoadState('load');
        await page.fill('#email', credentials.defaultShopCustomerEmail);
        await page.fill('#passwort', credentials.defaultShopCustomerPassword);
        await page.click('.register--login-btn');
        await expect(page).toHaveURL(/.*account/);
        await expect(page.locator('.account--welcome > .panel--title')).toHaveText(/.*Mustermann.*/);

        //Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32');
        await page.click('.buybox--button');

        //Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        //Change payment
        await page.click('.btn--change-payment');
        await page.click('text=Kauf auf Rechnung');
        await page.click('text=Weiter >> nth=1');

        await expect(page.locator('.payment--description')).toHaveText(/Kauf auf Rechnung/);

        await page.click('input[name="sAGB"]');
        await page.click('button:has-text("Zahlungspflichtig bestellen")');

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });
})
