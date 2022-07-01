export default (function () {
    return {
        /**
         * @param {FrameLocator} parentFrame
         */
        acceptCookies: async function (parentFrame) {
            const gdprCookieBanner = parentFrame.locator('#gdprCookieBanner');
            const acceptButton = gdprCookieBanner.locator('button#acceptAllButton');

            await gdprCookieBanner.waitFor({ state: 'attached' });
            await acceptButton.waitFor({ state: 'attached' });

            // Needed for playwright to wait for the slide-in animation to be finished.
            await acceptButton.scrollIntoViewIfNeeded();

            await acceptButton.click();
        }
    };
}());
