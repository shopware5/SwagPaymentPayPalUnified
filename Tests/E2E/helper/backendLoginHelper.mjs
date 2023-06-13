import credentials from '../test/credentials.mjs';
import {expect} from '@playwright/test';
import clearCacheHelper from "./clearCacheHelper.mjs";

export default (function() {
    return {
        login: async function(page) {
            await clearCacheHelper.clearCache();

            await page.goto('/backend', {waitUntil: 'load'});

            await expect(page).toHaveTitle(/Backend/);
            await expect(page.locator('#button-1019-btnEl')).toHaveText(/Login/);

            const userNameField = await page.locator('input[name="username"]');
            // Wait for the focus change of the shopware login
            await expect(userNameField).toBeFocused();

            await userNameField.type(credentials.defaultBackendUserUsername);
            await page.locator('input[name="password"]').type(credentials.defaultBackendUserPassword);

            await page.click('#button-1019-btnEl');

            await page.waitForLoadState('networkidle', { timeout: 5000 });
        }
    };
}());
