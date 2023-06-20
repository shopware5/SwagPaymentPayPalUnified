import { expect, test } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import fs from 'fs';
import path from 'path';
import backendLoginHelper from '../helper/backendLoginHelper.mjs';

const connection = MysqlFactory.getInstance();
const orderSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/order_for_check_tracking.sql'), 'utf8');
const clearOrderSql = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/clear_orders_for_check_tracking.sql'), 'utf8');

test.describe('Tracking url testing', () => {
    test('Check tracking button visibility behavior', async({ page }) => {
        await connection.query(clearOrderSql);
        await connection.query(orderSql);

        await backendLoginHelper.login(page);

        await page.hover('.customers--main');
        await page.click('.customers--orders');

        await page.waitForTimeout(1000);
        const popupCloseButtons = await page.$$('text=\'Schließen\'');
        for (let i = 0; i < popupCloseButtons.length; i++) {
            await popupCloseButtons[i].click();
        }
        await page.waitForTimeout(500);

        await page.waitForSelector('.sprite-pencil');
        const orders = await page.$$('.sprite-pencil');

        await orders.at(0).click();
        await page.waitForLoadState('load');

        await expect(page.locator('.paypalTrackingButton')).toHaveText(/Tracking Code zu Paypal hinzufügen/);
        await page.locator('.x-order-detail-window >> .x-tool-close').click();

        await orders.at(-1).click();
        await page.waitForLoadState('load');

        await expect(await page.$$('.paypalTrackingButton')).toHaveLength(0);

        await connection.query(clearOrderSql);
    });
});
