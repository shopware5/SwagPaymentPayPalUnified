import http from 'http';
import backendLoginHelper from './backendLoginHelper.mjs';

export default (function () {
    return {
        clearCache: function () {
            const options = {
                hostname: process.env.SW_HOST,
                port: 80,
                path: '/api/caches',
                method: 'DELETE',
                auth: 'demo:demo',
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            const req = http.request(options);

            req.on('error', error => {
                console.error(error);
            });

            req.write('');
            req.end();
        },

        clearShopwareCacheByUsingBackend: async function (page) {
            await backendLoginHelper.login(page);

            // open the performance module
            await page.locator('text=Einstellungen').click();
            await page.locator('text=Caches / Performance').click();

            await page.waitForLoadState('load');

            // select the cache tab
            await page.locator('.x-tab-inner:has-text("Cache")').click();

            // clear the cache
            await page.locator('button[role="button"]:has-text("Alle auswählen")').click();
            await page.locator('button[role="button"]:has-text("Leeren") >> visible=true').click();
            await page.locator('button[role="button"]:has-text("Themes kompilieren")').click();

            await page.waitForLoadState('load');
        }
    };
}());
