import { expect, test } from '@playwright/test';
import loginHelper from '../helper/loginHelper.mjs';
import customerCommentHelper from '../helper/customerCommentHelper.mjs';
import clearCacheHelper from '../helper/clearCacheHelper.mjs';

test.describe('Test voucher field is visible on checkout page if payment method is not PayPal-Express', () => {
    test('Check if voucher field is visible', async({ page }) => {
        await customerCommentHelper.updateCommentSetting();
        await clearCacheHelper.clearCache();

        await loginHelper.login(page);

        await page.goto('genusswelten/edelbraende/9/special-finish-lagerkorn-x.o.-32', { waitUntil: 'load' });
        await page.click('.buybox--button');

        // Go to checkout/cart
        await page.click('.button--open-basket');
        await expect(page).toHaveURL(/.*checkout\/cart/);

        if (await page.locator('text=Ich habe einen Gutschein').count() > 0) {
            await page.locator('text=Ich habe einen Gutschein').click();
        }

        // Check voucher field is visible
        await expect(page.locator('.add-voucher--field')).toBeVisible();

        // Go to checkout/confirm
        await page.click('.btn--checkout-proceed');
        await expect(page).toHaveURL(/.*checkout\/confirm/);

        // Check voucher field is visible
        await expect(page.locator('.add-voucher--field')).toBeVisible();
    });
});
