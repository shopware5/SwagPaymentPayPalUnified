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
             * @type string
             */
            createOrderUrlFallback: '',

            checkoutConfirmUrlFallback: ''
        },

        init: function() {
            this.applyDataAttributes();

            this.$form = $(this.opts.confirmFormSelector);

            this.disableConfirmButton();
            this.hideConfirmButton();

            this.disableTosCheckbox();

            this.renderButton();
        },

        hideConfirmButton: function() {
            var me = this;

            me.$confirmButton = $(me.opts.confirmFormSubmitButtonSelector).remove();
        },

        disableConfirmButton: function() {
            var me = this;

            me._on(me.$form, 'submit', $.proxy(me.onConfirmCheckout, me));
        },

        disableTosCheckbox: function () {
            var $panel = $('.tos--panel'),
                $checkbox = $panel.find('input#sAGB');

            $checkbox.attr('required', false);
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
            var me = this;

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            actions.redirect(me.renderConfirmUrl(data));
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

            return [this.opts.checkoutConfirmUrlFallback, '?', params].join('');
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        onPayPalAPIError: function() {
            window.location.replace(this.opts.paypalErrorPage);
        }
    });
})(jQuery, window);
