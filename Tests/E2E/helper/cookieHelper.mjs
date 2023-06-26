export default (function() {
    return {
        /**
         * @param { FrameLocator } parentFrame
         */
        acceptCookies: async function(parentFrame) {
            const gdprCookieBanner = await parentFrame.locator('#gdprCookieBanner');
            const acceptButton = await gdprCookieBanner.locator('button#acceptAllButton');

            if (await gdprCookieBanner.count() <= 0) {
                return;
            }

            await gdprCookieBanner.isVisible();
            await gdprCookieBanner.waitFor({ state: 'attached', timeout: 1000 });

            await acceptButton.waitFor({ state: 'attached', timeout: 1000 });

            // Needed for playwright to wait for the slide-in animation to be finished.
            await acceptButton.scrollIntoViewIfNeeded();

            await acceptButton.click();
        }
    };
}());
