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
            paypalIntent: 'capture',

            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

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
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * selector for the checkout confirm agb element
             *
             * @type string
             */
            agbCheckboxSelector: '#sAGB',

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

            this.$form = $(this.opts.confirmFormSelector);

            this.disableConfirmButton();
            this.hideConfirmButton();

            this.renderButton();
        },

        hideConfirmButton: function() {
            this.$confirmButton = $(this.opts.confirmFormSubmitButtonSelector).remove();
        },

        disableConfirmButton: function() {
            this._on(this.$form, 'submit', $.proxy(this.onConfirmCheckout, this));
        },

        /**
         * @param { Event } event
         */
        onConfirmCheckout: function(event) {
            event.preventDefault();
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
                 * Will be called on int the payment button
                 */
                onInit: this.onInitPayPalButton.bind(this),

                /**
                 * Will be called on the payment button is clicked
                 */
                onClick: this.onPayPalButtonClick.bind(this),

                /**
                 * Will be called after payment button is clicked
                 */
                createOrder: this.createOrder.bind(this),

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

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/createConfig', [this, buttonConfig]);

            return buttonConfig;
        },

        /**
         * @return { Promise }
         */
        createOrder: function() {
            var me = this;

            return $.ajax({
                method: 'get',
                url: me.opts.createOrderUrlFallback
            }).then(function(response) {
                me.opts.basketId = response.basketId;

                return response.paypalOrderId;
            }, function() {
            }).promise();
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
            var params = $.param({
                paypalOrderId: data.orderID,
                payerId: data.payerID,
                basketId: this.opts.basketId
            }, true);

            return [this.opts.fallbackReturnUrl, '?', params].join('');
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        onPayPalAPIError: function() {
            window.location.replace(this.opts.paypalErrorPage);
        },

        /**
         * @param data { Object }
         * @param actions { Object }
         */
        onInitPayPalButton: function (data, actions) {
            actions.disable();

            $(this.opts.agbCheckboxSelector).on('change', function (event) {
                if (event.target.checked) {
                    actions.enable();
                } else {
                    actions.disable();
                }
            });
        },

        onPayPalButtonClick: function () {
            this.$form[0].checkValidity();
        }
    });
})(jQuery, window);
