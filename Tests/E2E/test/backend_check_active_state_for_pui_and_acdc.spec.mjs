import { expect, test } from '@playwright/test';

import MysqlFactory from '../helper/mysqlFactory.mjs';
import credentials from './credentials.mjs';
import clearPaypalSettingsSql from '../helper/clearPaypalSettingsHelper.mjs';
import backendHandleSaveHelper from '../helper/backendHandleSaveHelper.mjs';
import backendLoginHelper from '../helper/backendLoginHelper.mjs';
import defaultPaypalSettingsSql from "../helper/paypalSqlHelper.mjs";
import clearCacheHelper from "../helper/clearCacheHelper.mjs";

const connection = MysqlFactory.getInstance();

test.describe('Check the active state of PUI and ACDC', () => {
    test.beforeEach(async() => {
        await connection.query(clearPaypalSettingsSql);
    });

    test('Check active state', async({ page }) => {
        await backendLoginHelper.login(page);
        await clearCacheHelper.makeCurl();

        await page.hover('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');

        const generalSettingsTabBody = page.locator('div[id^="paypal-unified-settings-tabs-general"][id$="body"]');
        const apiFieldset = generalSettingsTabBody.locator('fieldset[id^="fieldset"]', { hasText: 'API-Einstellungen' });

        const activateCheckbox = generalSettingsTabBody.locator('tr[id^="checkboxfield"]', { hasText: 'Für diesen Shop aktivieren' }).locator('input[id$="inputEl"]');
        const sandboxCheckbox = apiFieldset.locator('tr[id^="checkboxfield"]', { hasText: 'Sandbox aktivieren' }).locator('input[id$="inputEl"]');

        // Checkbox -> Activate for this shop: Activate this option to enable PayPal for this shop.
        await activateCheckbox.click();

        // Checkbox -> Activate sandbox: Activate this option if you want to test the integration.
        await sandboxCheckbox.click();

        await page.locator('input[name="sandboxClientId"]').scrollIntoViewIfNeeded();
        await page.fill('input[name="sandboxClientId"]', credentials.paypalSandboxClientId);
        await page.fill('input[name="sandboxClientSecret"]', credentials.paypalSandboxClientSecret);
        await page.fill('input[name="sandboxPaypalPayerId"]', credentials.paypalSandboxMerchantId);

        await backendHandleSaveHelper.save(page);

        const payUponInvoiceSettingsTab = page.locator('div.x-tab[id^="tab-"]', { hasText: 'Kauf auf Rechnung Integration' });
        const payUponInvoiceSettingsTabBody = page.locator('div[id^="paypal-unified-settings-tabs-pay-upon-invoice"][id$="body"]');
        const activationFieldset = payUponInvoiceSettingsTabBody.locator('fieldset[id^="fieldset"]', { hasText: 'Für diesen Shop aktivieren' });

        const activatePayUponInvoiceCheckboxTable = activationFieldset.locator('table[id^="checkboxfield"]', { hasText: 'Für diesen Shop aktivieren' });
        const activatePayUponInvoiceCheckbox = activatePayUponInvoiceCheckboxTable.locator('tr[id^="checkboxfield"]').locator('input[id$="inputEl"]');

        await payUponInvoiceSettingsTab.click();

        // Check Tab -> PayPal Pay Upon Invoice Integration -> is active
        await expect(payUponInvoiceSettingsTab).toHaveClass(/x-tab-top-active/);

        // Checkbox -> Activate this option to use PayPal Invoice Purchase for this shop -> should be checked
        await expect(activatePayUponInvoiceCheckboxTable).toHaveClass(/x-form-cb-checked/);

        await page.locator('textarea[name="customerServiceInstructions"]').type('This field is required if PayUponInvoice is onboarded');

        await backendHandleSaveHelper.save(page);

        // Deactivate Checkbox -> Activate this option to use PayPal Invoice Purchase for this shop
        await activatePayUponInvoiceCheckbox.click();

        // Test save twice
        await backendHandleSaveHelper.save(page);

        await expect(activatePayUponInvoiceCheckboxTable).not.toHaveClass(/x-form-cb-checked/);

        await backendHandleSaveHelper.save(page);

        await expect(activatePayUponInvoiceCheckboxTable).not.toHaveClass(/x-form-cb-checked/);
    });
});
