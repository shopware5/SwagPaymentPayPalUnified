import { expect, test } from '@playwright/test';

import MysqlFactory from '../helper/mysqlFactory.mjs';
import credentials from './credentials.mjs';
import clearPaypalSettingsSql from '../helper/clearPaypalSettingsHelper.mjs';
import backendHandleSaveHelper from '../helper/backendHandleSaveHelper.mjs';

const connection = MysqlFactory.getInstance();

test.use({ viewport: { width: 1920, height: 1080 } });

test.describe('Check the active state of PUI and ACDC', () => {
    test('Check active state', async ({ page }) => {
        connection.query(clearPaypalSettingsSql);

        await page.goto('/backend');
        await expect(page).toHaveTitle(/Backend/);

        await page.fill('input[name="username"]', credentials.defaultBackendUserUsername);
        await page.fill('input[name="password"]', credentials.defaultBackendUserPassword);
        await page.click('#button-1019-btnEl');

        await page.waitForLoadState('load');

        await page.click('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');

        // Checkbox -> Activate for this shop: Activate this option to enable PayPal for this shop.
        await page.click('//html/body/div[6]/div[3]/div[3]/div/div[2]/div/div/fieldset[1]/div/table/tbody/tr/td[2]/input');

        // Checkbox -> Activate sandbox: Activate this option if you want to test the integration.
        await page.click('//html/body/div[6]/div[3]/div[3]/div/div[2]/div/div/fieldset[2]/div/table/tbody/tr/td[2]/input');

        await page.locator('input[name="sandboxClientId"]').scrollIntoViewIfNeeded();
        await page.fill('input[name="sandboxClientId"]', credentials.paypalSandboxClientId);
        await page.fill('input[name="sandboxClientSecret"]', credentials.paypalSandboxClientSecret);
        await page.fill('input[name="sandboxPaypalPayerId"]', credentials.paypalSandboxMerchantId);

        await backendHandleSaveHelper.save(page);

        // Check Tab -> PayPal Pay Upon Invoice Integration -> is active
        await expect(page.locator('//html/body/div[6]/div[3]/div[3]/div/div[1]/div[1]/div[2]/div/div[5]')).toHaveClass(/x-tab-top-active/);

        // Checkbox -> Activate this option to use PayPal Invoice Purchase for this shop -> should be checked
        const activeCheckboxTable = page.locator('//html/body/div[6]/div[3]/div[3]/div/div[2]/div[2]/div[2]/fieldset[1]/div/table');
        await expect(activeCheckboxTable).toHaveClass(/x-form-cb-checked/);

        await page.locator('textarea[name="customerServiceInstructions"]').type('This field is required if PayUponInvoice is onboarded');

        await backendHandleSaveHelper.save(page);

        // Deactivate Checkbox -> Activate this option to use PayPal Invoice Purchase for this shop
        await page.click('//html/body/div[6]/div[3]/div[3]/div/div[2]/div[2]/div[2]/fieldset[1]/div/table/tbody/tr/td[2]/input');

        // Test save twice
        await backendHandleSaveHelper.save(page);

        await expect(activeCheckboxTable).not.toHaveClass(/x-form-cb-checked/);

        await backendHandleSaveHelper.save(page);

        await expect(activeCheckboxTable).not.toHaveClass(/x-form-cb-checked/);
    });
});
