import { test, expect } from '@playwright/test';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Pui should show error messages', () => {
    test.beforeEach(async() => {
        await connection.query(defaultPaypalSettingsSql);
    });

    test('Check if error messages was shown to the customer', async({ page }) => {
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const paymentMethodSelector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedPayUponInvoice
        );
        await page.locator(paymentMethodSelector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        await page.goto('checkout/confirm/puiBirthdateWrong/1', { waitUntil: 'load' });
        await expect(page.locator('.alert--content')).toHaveText(/Bitte geben Sie einen gültiges Datum für ihr Geburtsdatum an./);

        await page.goto('checkout/confirm?puiBirthdateWrong=1', { waitUntil: 'load' });
        await expect(page.locator('.alert--content')).toHaveText(/Bitte geben Sie einen gültiges Datum für ihr Geburtsdatum an./);

        await page.goto('checkout/confirm/puiPhoneNumberWrong/1', { waitUntil: 'load' });
        await expect(page.locator('.alert--content')).toHaveText(/Bitte geben Sie eine gültige Telefonnummer an./);

        await page.goto('checkout/confirm?puiPhoneNumberWrong=1', { waitUntil: 'load' });
        await expect(page.locator('.alert--content')).toHaveText(/Bitte geben Sie eine gültige Telefonnummer an./);
    });
});
