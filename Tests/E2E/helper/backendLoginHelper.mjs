import credentials from '../test/credentials.mjs';
import { expect } from '@playwright/test';

export default (function () {
    return {
        login: async function (page) {
            await page.goto('/backend', { waitUntil: 'load' });

            await expect(page).toHaveTitle(/Backend/);
            await expect(page.locator('#button-1019-btnEl')).toHaveText(/Login/);

            // Wait for the focus change of the shopware login
            await expect(page.locator('input[name="username"]')).toBeFocused();

            await page.fill('input[name="username"]', credentials.defaultBackendUserUsername);
            await page.fill('input[name="password"]', credentials.defaultBackendUserPassword);

            await page.click('#button-1019-btnEl');

            await page.waitForLoadState('load');
        }
    };
}());
