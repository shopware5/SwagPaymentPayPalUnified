;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedSmartPaymentButtons', {
        defaults: {
            /**
             * Determines whether only the marks are needed on the current page
             *
             * @type boolean
             */
            marksOnly: false,

            /**
             * The URL used to create the order
             *
             * @type string
             */
            createOrderUrl: '',

            /**
             * After approval, redirect to this URL
             *
             * @type string
             */
            checkoutConfirmUrl: '',

            /**
             * This page will be opened when the payment creation fails.
             *
             * @type string
             */
            paypalErrorPage: '',

            /**
             * The class name to identify whether the PayPal sdk has been loaded
             *
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

            /**
             * Holds the client id
             *
             * @type string
             */
            clientId: '',

            /**
             * @type string
             */
            paypalIntent: 'capture',

            /**
             * The language ISO (ISO_639) or the Smart Payment Buttons.
             *
             * for possible values see: https://developer.paypal.com/api/rest/reference/locale-codes/
             *
             * @type string
             */
            locale: 'en_GB',

            /**
             * Use PayPal sandbox
             *
             * @type boolean
             */
            useSandbox: false,

            /**
             * Currency which should be used for the Smart Payment Buttons
             *
             * @type string
             */
            currency: 'EUR',

            /**
             * The unique ID of the basket. Will be generated on creating the payment
             *
             * @type string
             */
            basketId: ''
        },

        /**
         * PayPal Object
         */
        paypal: {},

        init: function() {
            var me = this;

            me.applyDataAttributes();
            me.subscribeEvents();
            $.publish('plugin/swagPayPalUnifiedSmartPaymentButtons/init', me);

            me.createButtons();

            $.publish('plugin/swagPayPalUnifiedSmartPaymentButtons/buttonsCreated', me);
        },

        /**
         * Subscribes the events that are required to run this instance.
         *
         * @private
         * @method subscribeEvents
         */
        subscribeEvents: function() {
            var me = this;

            $.subscribe(me.getEventName('plugin/swShippingPayment/onInputChanged'), $.proxy(me.createButtons, me));
        },

        createButtons: function() {
            var me = this,
                $head = $('head');

            if (!$head.hasClass(this.opts.paypalScriptLoadedSelector)) {
                $.ajax({
                    url: this.renderSdkUrl(),
                    dataType: 'script',
                    cache: true,
                    success: function() {
                        $head.addClass(me.opts.paypalScriptLoadedSelector);
                        me.paypal = window.paypal;
                        me.renderButtons();
                    }
                });
            } else {
                this.paypal = window.paypal;
                this.renderButtons();
            }
        },

        renderSdkUrl: function() {
            var params = {
                'client-id': this.opts.clientId,
                intent: this.opts.paypalIntent.toLowerCase()
            };

            /**
             * If marks only are displayed, remove unnecessary parameters
             * But still load buttons and marks so the buttons are present on the window PayPal object
             */
            if (this.opts.marksOnly) {
                params.components = 'marks';
            } else {
                params.components = 'marks,buttons';
                params.commit = false;
                params.currency = this.opts.currency;
            }

            if (this.opts.locale.length > 0) {
                params.locale = this.opts.locale;
            }

            if (this.opts.useSandbox) {
                params.debug = true;
            }

            return [this.opts.sdkUrl, '?', $.param(params, true)].join('');
        },

        renderButtons: function() {
            var me = this,
                buttonConfig = me.getButtonConfig(),
                el = me.$el.get(0);

            // Render the marks for each element visible with the id spbMarksContainer
            $('[id=spbMarksContainer]:visible').each(function() {
                me.paypal.Marks().render(this);
            });

            if (me.opts.marksOnly) {
                return;
            }

            me.paypal.Buttons(buttonConfig).render(el);
        },

        getButtonConfig: function() {
            return {
                style: {
                    label: 'checkout'
                },

                /**
                 * Will be called if on smarty payment button is clicked
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
        },

        /**
         * @return { Promise }
         */
        createOrder: function() {
            var me = this;

            return $.ajax({
                method: 'get',
                url: me.opts.createOrderUrl
            }).then(function(response) {
                me.opts.basketId = response.basketId;

                return response.paypalOrderId;
            }, function() {
            }).promise();
        },

        onApprove: function(data, actions) {
            var confirmUrl = this.opts.checkoutConfirmUrl + '?' + $.param({
                paypalOrderId: data.orderID,
                payerId: data.payerID,
                basketId: this.opts.basketId
            }, true);

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            actions.redirect(confirmUrl);
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        onPayPalAPIError: function() {
            window.location.replace(this.opts.paypalErrorPage);
        },

        destroy: function() {
            var me = this;
            me._destroy();
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSmartPaymentButtons="true"]', 'swagPayPalUnifiedSmartPaymentButtons');
})(jQuery, window);
