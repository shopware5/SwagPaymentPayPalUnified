import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import tryUntilSucceed from '../helper/retryHelper.mjs';
import offCanvasSettingHelper from '../helper/offCanvasSettingHelper.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Is Express Checkout button available', () => {
    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test.beforeAll(async () => {
        await offCanvasSettingHelper.deactivateOffCanvasCart();
        await clearCacheHelper.clearCache();
    });

    test.afterAll(async () => {
        await offCanvasSettingHelper.activateOffCanvas();
        await clearCacheHelper.clearCache();
    });

    test('Check product cart modal @notIn5.2', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza', { waitUntil: 'load' });

        await page.locator('text=In den Warenkorb').click();
        await page.waitForLoadState('load');

        const locator = await page.frameLocator('.js--modal .component-frame').locator('.paypal-button');
        await expect(locator).toHaveText(/Direkt zu/);

        await page.waitForTimeout(1000);

        const [paypalPage] = await tryUntilSucceed(() => {
            return Promise.all([
                page.waitForEvent('popup'),
                locator.dispatchEvent('click')
            ]);
        });

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);
    });
});
