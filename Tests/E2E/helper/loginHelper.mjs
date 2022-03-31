import credentials from '../test/credentials.mjs';
import { expect } from '@playwright/test';

export default (function () {
    return {
        login: async function (page) {
            // login
            await page.goto('/account');
            await page.waitForLoadState('load');

            await page.fill('#email', credentials.defaultShopCustomerEmail);
            await page.fill('#passwort', credentials.defaultShopCustomerPassword);

            await page.click('.register--login-btn');

            await expect(page).toHaveURL(/.*account/);
            await expect(page.locator('h1[class="panel--title"]')).toHaveText(/.*Mustermann.*/);
        }
    };
}());
