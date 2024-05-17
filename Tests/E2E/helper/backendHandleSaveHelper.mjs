export default (function () {
    return {
        save: async function (page) {
            await Promise.all([
                page.waitForResponse(/.*isCapable.*/),
                page.click('text=Speichern')
            ]);

            await Promise.all([
                page.waitForResponse(/.*PaypalUnifiedSettings.*/),
                // page.waitForResponse(/.*PaypalUnifiedExpressSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedPlusSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedInstallmentsSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedPayUponInvoiceSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedAdvancedCreditDebitCardSettings.*/)
            ]);
        },

        saveWithoutPayerId: async function (page) {
            await Promise.all([
                page.waitForResponse(/.*PaypalUnifiedSettings.*/),
                // page.waitForResponse(/.*PaypalUnifiedExpressSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedPlusSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedInstallmentsSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedPayUponInvoiceSettings.*/),
                page.waitForResponse(/.*PaypalUnifiedAdvancedCreditDebitCardSettings.*/),
                page.click('text=Speichern')
            ]);
        }
    };
}());
