import { test, expect } from '@playwright/test';

test.describe("Is Express Checkout button available", () => {

    test('Check product detail page', async ({ page }) => {

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        let locator = await page.frameLocator('.component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.click(),
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/)
    
    });

    test('Check offcanvas cart', async ({ page }) => {

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);

        let locator = await page.frameLocator('.ajax--cart >> .component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.click(),
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/)
    
    });

    test('Check checkout cart page', async ({ page }) => {

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Warenkorb bearbeiten/);
        await page.locator('text=Warenkorb bearbeiten').click();
        await expect(page).toHaveURL('/checkout/cart');

        let locator = await page.frameLocator('.component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.click(),
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/)
    
    });

    test('Check register page', async ({ page }) => {

        await page.goto('/sommerwelten/beachwear/178/strandtuch-ibiza');

        await page.locator('text=In den Warenkorb').click();
        await expect(page.locator('.ajax--cart')).toHaveText(/Zur Kasse/);
        await page.locator('text=Zur Kasse').click();
        await expect(page).toHaveURL('/checkout/confirm');

        let locator = await page.frameLocator('.component-frame').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click'),
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/)
    
    });

    test('Check product listing page', async ({ page }) => {

        await page.goto('/sommerwelten/beachwear/');

        let locator = await page.frameLocator('.component-frame >> nth=1').locator('div[role="button"]');
        await expect(locator).toHaveText(/Direkt zu/);
        await page.waitForLoadState('load');

        const [paypalPage] = await Promise.all([
            page.waitForEvent('popup'),
            locator.dispatchEvent('click'),
        ]);

        await expect(paypalPage.locator('#headerText')).toHaveText(/PayPal/)
    
    });

})
