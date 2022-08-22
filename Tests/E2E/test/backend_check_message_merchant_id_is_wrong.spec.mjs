import { expect, test } from '@playwright/test';

import MysqlFactory from '../helper/mysqlFactory.mjs';
import clearPaypalSettingsSql from '../helper/clearPaypalSettingsHelper.mjs';
import backendHandleSaveHelper from '../helper/backendHandleSaveHelper.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import backendLoginHelper from '../helper/backendLoginHelper.mjs';

const connection = MysqlFactory.getInstance();

test.use({ viewport: { width: 1920, height: 1080 } });

test.describe('Check for a readable message if the merchant Id is wrong', () => {
    test('Check for a readable message', async ({ page }) => {
        connection.query(clearPaypalSettingsSql);
        connection.query(defaultPaypalSettingsSql);

        backendLoginHelper.login(page);

        await page.hover('.customers--main');
        await page.hover('.settings--payment-methods');
        await page.hover('.sprite--paypal-unified');
        await page.click('.settings--basic-settings');

        await page.locator('input[name="sandboxClientId"]').scrollIntoViewIfNeeded();
        await page.fill('input[name="sandboxPaypalPayerId"]', 'abcdefghijklmnop');

        await backendHandleSaveHelper.saveWithoutPayerId(page);

        const growlMessage = page.locator('.growl-msg-sticky-notification', { hasText: 'Ihr Paypal Konto konnte nicht verknüpft werden' });
        await expect(growlMessage.isVisible()).toBeTruthy();
        await growlMessage.locator('text=Schließen').click();

        await page.click('text=PayPal Pay Upon Invoice Integration');
        await page.locator('text=Verfügbarkeitstest >> visible=true').click();

        await expect(growlMessage.isVisible()).toBeTruthy();
        await growlMessage.locator('text=Schließen').click();

        await page.click('text=PayPal Advanced Credit Debit Card Integration');
        await page.locator('text=Verfügbarkeitstest >> visible=true').click();

        await expect(growlMessage.isVisible()).toBeTruthy();
        await growlMessage.locator('text=Schließen').click();
    });
});
