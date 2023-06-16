import {test, expect} from '@playwright/test';
import defaultPaypalSettingsSql from '../helper/paypalSqlHelper.mjs';
import MysqlFactory from '../helper/mysqlFactory.mjs';
import loginHelper from '../helper/loginHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';
import cookieHelper from '../helper/cookieHelper.mjs';

import getPaypalPaymentMethodSelector from '../helper/getPayPalPaymentMethodSelector.mjs';
import credentials from "./credentials.mjs";

const connection = MysqlFactory.getInstance();

test.describe('Pay with credit card', () => {
    test.beforeEach(async({ page }) => {
        connection.query(defaultPaypalSettingsSql);
        await clearCacheHelper.clearCache();
    });

    test('Buy a product with a credit card wich is secured and complete the payment process', async({ page }) => {
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
            getPaypalPaymentMethodSelector.paymentMethodNames.SwagPaymentPayPalUnifiedAdvancedCreditDebitCard
        );

        if (!await page.locator(selector).isChecked()) {
            await page.locator(selector).check();
            await page.waitForLoadState('load');
        }

        await page.click('text=Weiter >> nth=1');
        await page.waitForLoadState('load');

        await page.waitForTimeout(1000);
        if (await page.getByText(/Invalides Formular-Token!/).count() > 0) {
            await page.goBack();
            await page.click('text=Weiter >> nth=1');
            await page.waitForLoadState('load');
        }

        await expect(page.locator('.payment--description')).toHaveText('Kredit- oder Debitkarte');
        await page.click('input[name="sAGB"]');

        const numberFieldFrame = await page.frameLocator('#braintree-hosted-field-number');
        await numberFieldFrame.locator('#credit-card-number').type(credentials.paypalCreditCard);

        const expirationDateField = await page.frameLocator('#braintree-hosted-field-expirationDate');
        await expirationDateField.locator('#expiration').type('0530');

        const cvvField = await page.frameLocator('#braintree-hosted-field-cvv');
        await cvvField.locator('#cvv').type('123');

        await page.waitForLoadState('load');

        await page.waitForTimeout(1000);

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await page.waitForLoadState('load');

        const contingencyHandlerIFrame = await page.frameLocator('iframe[title~="payments_sdk_contingency_handler"]');
        const threeDSecureIFrame = await contingencyHandlerIFrame.frameLocator('iframe[id="threedsIframeV2"]');
        const cardinalStepUpIFrame = await threeDSecureIFrame.frameLocator('iframe[id^="cardinal-stepUpIframe"]');
        const submitTokenForm = await cardinalStepUpIFrame.locator('form[name="cardholderInput"]');

        await page.waitForTimeout(1000);

        await cookieHelper.acceptCookies(contingencyHandlerIFrame);

        const submitTokenInput = submitTokenForm.locator('input[name="challengeDataEntry"]');
        const submitButton = await submitTokenForm.locator('input[value="SUBMIT"]');
        await submitTokenInput.fill('1234');
        await submitButton.click();

        await expect(page.locator('.teaser--title')).toHaveText(/Vielen Dank für Ihre Bestellung bei Shopware Demo/);
    });

    test('Buy a product with a credit card wich is secured and abort the payment process', async({ page }) => {
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

        await page.waitForTimeout(1000);
        if (await page.getByText(/Invalides Formular-Token!/).count() > 0) {
            await page.goBack();
            await page.click('text=Weiter >> nth=1');
            await page.waitForLoadState('load');
        }

        await expect(page.locator('.payment--description')).toHaveText('Kredit- oder Debitkarte');
        await page.click('input[name="sAGB"]');

        const numberFieldFrame = await page.frameLocator('#braintree-hosted-field-number');
        await numberFieldFrame.locator('#credit-card-number').type(credentials.paypalCreditCard);

        const expirationDateField = await page.frameLocator('#braintree-hosted-field-expirationDate');
        await expirationDateField.locator('#expiration').type('0530');

        const cvvField = await page.frameLocator('#braintree-hosted-field-cvv');
        await cvvField.locator('#cvv').type('123');

        await page.waitForLoadState('load');

        await page.waitForTimeout(1000);

        await page.click('button:has-text("Zahlungspflichtig bestellen")');
        await page.waitForLoadState('load');

        const contingencyHandlerIFrame = await page.frameLocator('iframe[title~="payments_sdk_contingency_handler"]');
        const threeDSecureIFrame = await contingencyHandlerIFrame.frameLocator('iframe[id="threedsIframeV2"]');
        const cardinalStepUpIFrame = await threeDSecureIFrame.frameLocator('iframe[id^="cardinal-stepUpIframe"]');
        const cancelForm = await cardinalStepUpIFrame.locator('form[name="cancel"]');

        await page.waitForTimeout(1000);
        const cancelButton = await cancelForm.locator('input[value="CANCEL"]');
        await cookieHelper.acceptCookies(contingencyHandlerIFrame);
        await cancelButton.click();

        const contingencyHandlerWrapper = await page.locator('div[id~="payments-sdk-contingency-handler"]');
        await contingencyHandlerWrapper.waitFor({ state: 'detached' });

        await expect(page.locator('.step--confirm.is--active')).toBeVisible();

        const paypalUnifiedErrorMessageContainer = await page.locator('.paypal-unified--error');
        await expect(paypalUnifiedErrorMessageContainer).toBeVisible();

        const messageContainer = await page.locator('.paypal-unified--error >> .alert--content');
        await messageContainer.scrollIntoViewIfNeeded();

        await expect(messageContainer).toHaveText(/.*Während der Sicherheitsüberprüfung Ihrer Kreditkarte ist etwas schief gelaufen. Bitte versuchen Sie es erneut.*/);
    });
});
