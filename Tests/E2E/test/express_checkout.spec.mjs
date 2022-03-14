import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import fs from 'fs';
import path from 'path';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
const connection = MysqlFactory.getInstance();
const truncateTables = fs.readFileSync(path.join(path.resolve(''), 'setup/sql/truncate_paypal_tables.sql'), 'utf8');

test.describe('Is Express Checkout button available', () => {
    test.beforeEach(() => {
        connection.query(truncateTables);
        connection.query(defaultPaypalSettingsSql);
    });

    test('Check product detail page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        const locator = await page.frameLocator('.component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.click()
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);
    });

    test('Check offcanvas cart', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);

        const locator = await page.frameLocator('.ajax--cart >> .component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.click()
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);
    });

    test('Check checkout cart page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);
        await page.locator('text=Warenkorb bearbeiten').click();
        await expect(page).toHaveURL('/checkout/cart');

        const locator = await page.frameLocator('.component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.click()
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);
    });

    test('Check register page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Zur Kasse/);
        await page.locator('text=Zur Kasse').click();
        await expect(page).toHaveURL('/checkout/confirm');

        const locator = await page.frameLocator('.component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);
    });

    test('Check product listing page', async ({ page }) => {
        await page.goto('/sommerwelten/beachwear/');

        const locator = await page.frameLocator('.component-frame >> nth=1').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/);
    });
});
