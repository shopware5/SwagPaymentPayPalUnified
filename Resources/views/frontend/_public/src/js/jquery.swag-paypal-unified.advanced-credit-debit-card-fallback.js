;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedAdvancedCreditDebitCardFallback', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
            /**
             * @type string
             */
            createOrderUrlFallback: '',

            /**
             * @type string
             */
            fallbackReturnUrl: ''
        }),

        init: function() {
            this.applyDataAttributes();

            this.createOrderFunction = $.createSwagPaymentPaypalCreateOrderFunction(this.opts.createOrderUrlFallback, this);
            this.formValidityFunctions = $.createSwagPaymentPaypalFormValidityFunctions(
                this.opts.confirmFormSelector,
                this.opts.confirmFormSubmitButtonSelector,
                this.opts.hiddenClass,
                'swagPayPalUnifiedAdvancedCreditDebitCardFallback'
            );

            this.cancelPaymentFunction = $.createCancelPaymentFunction();

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.buttonSize = $.swagPayPalCreateButtonSizeObject(this.opts);

            this.renderButton();
        },

        renderButton: function() {
            this.paypal = window.paypal;

            var buttonConfig = this.getButtonConfig(),
                el = $('.main--actions'),
                buttonContainer = $('<div class="paypal-unified--smart-payment-buttons acdc">');

            buttonContainer.addClass(this.buttonSize[this.opts.size].widthClass);

            el.append(buttonContainer);

            this.paypal.Buttons(buttonConfig).render(buttonContainer.get(0));
        },

        /**
         * @return { Object }
         */
        getButtonConfig: function() {
            var buttonConfig = {
                fundingSource: 'card',

                style: $.swagPayPalCreateButtonStyle(this.opts, this.buttonSize, false),

                /**
                 * Will be called on initialisation of the payment button
                 */
                onInit: this.formValidityFunctions.onInitPayPalButton.bind(this.formValidityFunctions),

                /**
                 * Will be called if the payment button is clicked
                 */
                onClick: this.formValidityFunctions.onPayPalButtonClick.bind(this.formValidityFunctions),

                /**
                 * Will be called after payment button is clicked
                 */
                createOrder: this.createOrderFunction.createOrder.bind(this.createOrderFunction),

                /**
                 * Will be called if the payment process is approved by PayPal
                 */
                onApprove: this.onApprove.bind(this),

                /**
                 * Will be called if the payment process is cancelled by the customer
                 */
                onCancel: this.cancelPaymentFunction.onCancel.bind(this.cancelPaymentFunction),

                /**
                 * Will be called if any api error occurred
                 */
                onError: this.onPayPalAPIError.bind(this)
            };

            $.publish('plugin/swagPayPalUnifiedAdvancedCreditDebitCardFallback/createConfig', [this, buttonConfig]);

            return buttonConfig;
        },

        /**
         * @return { Promise }
         */
        onApprove: function(data, actions) {
            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            var url = this.renderConfirmUrl(data);
            $.redirectToUrl(url);
        },

        /**
         * @param data { Object }
         *
         * @return { string }
         */
        renderConfirmUrl: function(data) {
            var params = {
                token: data.orderID,
                payerId: data.payerID,
                basketId: this.opts.basketId
            };

            return $.swagPayPalRenderUrl(this.opts.fallbackReturnUrl, params);
        },

        onPayPalAPIError: function() {
            $.redirectToUrl(this.opts.paypalErrorPage);
        }
    });
})(jQuery, window);
