export default (function() {
    return {
        /**
         * @param {FrameLocator} parentFrame
         */
        acceptCookies: async function(parentFrame) {
            const gdprCookieBanner = parentFrame.locator('#gdprCookieBanner');
            const acceptButton = gdprCookieBanner.locator('button#acceptAllButton');

            if (await gdprCookieBanner.isVisible()) {
                await gdprCookieBanner.waitFor({ state: 'attached', timeout: 1000 });
            }

            if (await acceptButton.isVisible()) {
                await acceptButton.waitFor({ state: 'attached', timeout: 1000 });

                // Needed for playwright to wait for the slide-in animation to be finished.
                await acceptButton.scrollIntoViewIfNeeded();

                await acceptButton.click();
            }
        }
    };
}());
