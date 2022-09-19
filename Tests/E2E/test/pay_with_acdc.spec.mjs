import { test, expect } from '@playwright/test';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import cookieHelper from '../helper/cookieHelper.mjs';

import { locators } from '../helper/scenario/pay_with_card/locatorHelper.mjs';
import scenarioHelper from '../helper/scenario/pay_with_card/scenarioHelper.mjs';
import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';

const connection = MysqlFactory.getInstance();

test.describe('Pay with credit card', () => {
    test.beforeAll(() => {
        clearCacheHelper.clearCache();
    });

    test.beforeEach(({ page }) => {
        connection.query(defaultPaypalSettingsSql);
        locators.init(page);
    });

    test('Buy a product with a credit card wich is secured and complete the payment process', async ({ page }) => {
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedAdvancedCreditDebitCard
        );

        if (!await page.locator(selector).isChecked()) {
            await page.locator(selector).check();
            await page.waitForLoadState('load');
        }

        await page.click('text=Weiter >> nth=1');
        await page.waitForLoadState('load');

        await expect(page.locator('.payment--description')).toHaveText('Kredit- oder Debitkarte');
        await page.click('input[name="sAGB"]');

        await page.frameLocator('#braintree-hosted-field-number').locator('#credit-card-number').type('5192507571573295');
        await page.frameLocator('#braintree-hosted-field-expirationDate').locator('#expiration').type('0530');
        await page.frameLocator('#braintree-hosted-field-cvv').locator('#cvv').type('123');

        await page.waitForLoadState('load');

        await page.waitForTimeout(5000);

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await page.waitForLoadState('load');

        await cookieHelper.acceptCookies(locators.contingencyHandlerIFrame);

        const infoText = await locators.cardinalStepUpIFrame.locator('p.challengeinfotext').textContent();
        const threeDSecureToken = scenarioHelper.readThreeDSecureToken(infoText);

        await locators.submitTokenForm.scrollIntoViewIfNeeded();

        await locators.submitTokenInput.fill(threeDSecureToken);
        await locators.submitButton.click();

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Buy a product with a credit card wich is secured and abort the payment process', async ({ page }) => {
        await loginHelper.login(page);

        // Buy Product
        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout
        await page.click('.button--checkout');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Change payment
        await page.click('.btn--change-payment');
        const selector = await getPaypalPaymentMethodSelector.getSelector(
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedAdvancedCreditDebitCard
        );

        if (!await page.locator(selector).isChecked()) {
            await page.locator(selector).check();
            await page.waitForLoadState('load');
        }

        await page.click('text=Weiter >> nth=1');
        await page.waitForLoadState('load');

        await expect(page.locator('.payment--description')).toHaveText('Kredit- oder Debitkarte');
        await page.click('input[name="sAGB"]');

        await page.frameLocator('#braintree-hosted-field-number').locator('#credit-card-number').type('5192507571573295');
        await page.frameLocator('#braintree-hosted-field-expirationDate').locator('#expiration').type('0530');
        await page.frameLocator('#braintree-hosted-field-cvv').locator('#cvv').type('123');

        await page.waitForLoadState('load');

        await page.waitForTimeout(5000);

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await page.waitForLoadState('load');

        await cookieHelper.acceptCookies(locators.contingencyHandlerIFrame);

        await locators.cancelForm.scrollIntoViewIfNeeded();
        await locators.cancelButton.click();

        await locators.contingencyHandlerWrapper.waitFor({ state: 'detached' });
        await page.waitForResponse(/.*www.sandbox.paypal.com.*\/session\/patchThreeds.*/);

        await expect(page.locator('.step--confirm.is--active')).toBeVisible();

        await locators.paypalUnifiedErrorMessageContainer.scrollIntoViewIfNeeded();

        await expect(locators.paypalUnifiedErrorMessageContainer).toBeVisible();
        await expect(locators.paypalUnifiedErrorMessageContainer).toHaveText('Die Zahlung konnte nicht verarbeitet werden. Bitte wählen Sie eine andere Zahlungsart.');
    });
});
