export const locators = (function () {
    return {
        /**
         * @var {Locator}
         */
        contingencyHandlerWrapper: null,

        /**
         * @var {FrameLocator}
         */
        contingencyHandlerIFrame: null,

        /**
         * @var {FrameLocator}
         */
        threeDSecureIFrame: null,

        /**
         * @var {FrameLocator}
         */
        cardinalStepUpIFrame: null,

        /**
         * @var {Locator}
         */
        submitTokenForm: null,

        /**
         * @var {Locator}
         */
        resendTokenForm: null,

        /**
         * @var {Locator}
         */
        cancelForm: null,

        /**
         * @var {Locator}
         */
        submitTokenInput: null,

        /**
         * @var {Locator}
         */
        submitButton: null,

        /**
         * @var {Locator}
         */
        resendButton: null,

        /**
         * @var {Locator}
         */
        cancelButton: null,

        /**
         * @var {Locator}
         */
        paypalUnifiedErrorMessageContainer: null,

        /**
         * @param {Page} page
         */
        init: function (page) {
            this.contingencyHandlerWrapper = page.locator('div[id~="payments-sdk-contingency-handler"]');
            this.contingencyHandlerIFrame = page.frameLocator('iframe[title~="payments_sdk_contingency_handler"]');
            this.threeDSecureIFrame = this.contingencyHandlerIFrame.frameLocator('iframe[id="threedsIframeV2"]');
            this.cardinalStepUpIFrame = this.threeDSecureIFrame.frameLocator('iframe[id^="cardinal-stepUpIframe"]');
            this.submitTokenForm = this.cardinalStepUpIFrame.locator('form[name="cardholderInput"]');
            this.resendTokenForm = this.cardinalStepUpIFrame.locator('form[name="resendChallengeData"]');
            this.cancelForm = this.cardinalStepUpIFrame.locator('form[name="cancel"]');
            this.submitTokenInput = this.submitTokenForm.locator('input[name="challengeDataEntry"]');
            this.submitButton = this.submitTokenForm.locator('input[type="submit"]');
            this.resendButton = this.resendTokenForm.locator('input[type="submit"]');
            this.cancelButton = this.cancelForm.locator('input[type="submit"]');
            this.paypalUnifiedErrorMessageContainer = page.locator('.paypal-unified--error');
        },
    };
})();
