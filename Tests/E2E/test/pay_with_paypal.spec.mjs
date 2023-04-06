import { test, expect } from '@playwright/test';
import credentials from './credentials.mjs';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import customerCommentHelper from '../helper/customerCommentHelper.mjs';
import restoreOrderNumberHelper from '../helper/restoreOrderNumberHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Frontend', () => {
    test.beforeAll(async () => {
        await clearCacheHelper.clearCache();
    });

    test.beforeEach(() => {
        connection.query(defaultPaypalSettingsSql);
    });

    test('Cancel payment', async ({ page }) => {
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        const changePaymentButton = await page.locator('.btn--change-payment');
        await expect(changePaymentButton).toHaveText('Zahlung und Versand ändern');

        // Change payment
        await changePaymentButton.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnified
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button:has-text("Jetzt kaufen")');
        await page.waitForLoadState('load');

        // check: can not check out without accept AGBs
        await locator.dispatchEvent('click');
        await expect(page.locator('label[for="sAGB"]')).toHaveClass('has--error');

        await page.click('input[name="sAGB"]');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);

        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);

        await paypalPage.locator('#btnLogin').click();

        await paypalPage.locator('text=Abbrechen und zurück zu Test Store.').click();

        await page.goto('genusswelten/koestlichkeiten/272/spachtelmasse?c=15', { waitUntil: 'load' });

        const restoredOrderNumbers = await restoreOrderNumberHelper.getRestoredOrderNumber();

        await expect(restoredOrderNumbers.length === 1).toBeTruthy();
    });

    test('Buy a product with paypal', async ({ page }) => {
        // activate customer comments
        await customerCommentHelper.updateCommentSetting();

        await clearCacheHelper.clearCache();

        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        const changePaymentButton = await page.locator('.btn--change-payment');
        await expect(changePaymentButton).toHaveText('Zahlung und Versand ändern');

        // Change payment
        await changePaymentButton.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnified
        );
        await page.locator(selector).check();
        await page.waitForLoadState('load');
        await page.click('text=Weiter >> nth=1');

        const locator = await page.frameLocator('.component-frame').locator('.paypal-button:has-text("Jetzt kaufen")');
        await page.waitForLoadState('load');

        // add a customer comment for a later check
        const commentField = await page.locator('.user-comment--field');
        await commentField.scrollIntoViewIfNeeded();
        await commentField.type('This is a customer comment');

        // check: can not check out without accept AGBs
        await locator.dispatchEvent('click');
        await expect(page.locator('label[for="sAGB"]')).toHaveClass('has--error');

        await page.click('input[name="sAGB"]');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click')
        ]);

        await paypalPage.locator('#email').fill(credentials.paypalCustomerEmail);

        await paypalPage.locator('#password').fill(credentials.paypalCustomerPassword);

        await paypalPage.locator('#btnLogin').click();

        // Click [data-testid="submit-button-initial"]
        await paypalPage.locator('button:has-text("Kauf abschließen"), button:has-text("Jetzt zahlen")').click();

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);

        // check if customer comment is written in the s_order table
        const comment = await customerCommentHelper.getCustomerComment();
        await expect(comment === 'This is a customer comment').toBeTruthy();
    });
});
