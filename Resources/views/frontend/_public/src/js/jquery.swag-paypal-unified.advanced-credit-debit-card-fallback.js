;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedAdvancedCreditDebitCardFallback', {
        defaults: {
            /**
             * PayPal button label
             *
             * IMPORTANT: Changing this value can lead to legal issues!
             *
             * @type string
             */
            label: 'buynow',

            /**
             * @type boolean
             */
            tagline: false,

            /**
             * @type string
             */
            size: 'medium',

            /**
             * @type string
             */
            shape: 'rect',

            /**
             * Possible black, white
             *
             * @type string
             */
            color: 'black',

            /**
             *  @type string
             */
            layout: 'horizontal',

            /**
             * The PayPal clientId
             *
             * @type string|null
             */
            clientId: null,

            /**
             * @type string
             */
            paypalErrorPage: '',

            /**
             * @type string
             */
            confirmFormSelector: '#confirm--form',

            /**
             * @type string
             */
            confirmFormSubmitButtonSelector: ':submit[form="confirm--form"]',

            /**
             * @type string
             */
            hiddenClass: 'is--hidden',

            /**
             * @type string
             */
            createOrderUrlFallback: '',

            /**
             * @type string
             */
            fallbackReturnUrl: ''
        },

        init: function() {
            this.applyDataAttributes();

            this.createOrderFunction = $.createSwagPaymentPaypalCreateOrderFunction(this.opts.createOrderUrlFallback, this);
            this.formValidityFunctions = $.createSwagPaymentPaypalFormValidityFunctions(
                this.opts.confirmFormSelector,
                this.opts.confirmFormSubmitButtonSelector,
                this.opts.hiddenClass,
                'swagPayPalUnifiedAdvancedCreditDebitCardFallback'
            );

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.renderButton();
        },

        renderButton: function() {
            this.paypal = window.paypal;

            var buttonConfig = this.getButtonConfig(),
                el = $('.main--actions'),
                buttonContainer = $('<div class="paypal-unified--smart-payment-buttons acdc">');

            el.append(buttonContainer);

            this.paypal.Buttons(buttonConfig).render(buttonContainer.get(0));
        },

        /**
         * @return { Object }
         */
        getButtonConfig: function() {
            var buttonConfig = {
                fundingSource: 'card',

                style: {
                    label: this.opts.label,
                    color: this.opts.color,
                    shape: this.opts.shape,
                    layout: this.opts.layout,
                    tagline: this.opts.tagline
                },

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
                onCancel: this.onCancel.bind(this),

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

            actions.redirect(this.renderConfirmUrl(data));
        },

        /**
         * @param data { Object }
         *
         * @return { string }
         */
        renderConfirmUrl: function(data) {
            var params = {
                paypalOrderId: data.orderID,
                payerId: data.payerID,
                basketId: this.opts.basketId
            };

            return $.swagPayPalRenderUrl(this.opts.fallbackReturnUrl, params);
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        onPayPalAPIError: function() {
            window.location.replace(this.opts.paypalErrorPage);
        }
    });
})(jQuery, window);
