;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedSepa', {
        defaults: {
            /**
             * Determines whether or not only the marks are needed on the current page
             *
             * @type boolean
             */
            marksOnly: false,

            /**
             *  @type string
             */
            layout: 'horizontal',

            /**
             * @type string
             */
            size: 'medium',

            /**
             * @type string
             */
            shape: 'rect',

            /**
             * The language ISO (ISO_639) locale of the button.
             *
             * for possible values see: https://developer.paypal.com/api/rest/reference/locale-codes/
             *
             * @type string
             */
            locale: '',

            /**
             * @type boolean
             */
            tagline: false,

            /**
             * The URL used to create the payment
             *
             * @type string
             */
            createPaymentUrl: '',

            /**
             * After approval, redirect to this URL
             *
             * @type string
             */
            checkoutConfirmUrl: '',

            /**
             * The class name to identify whether or not the paypal sdk has been loaded
             *
             * @type string
             */
            scriptLoadedClass: 'paypal-unified--paypal-sdk-loaded',

            /**
             * Holds the client id
             *
             * @type string
             */
            clientId: '',

            /**
             * The language ISO (ISO_639) for the Smart Payment Buttons.
             *
             * @type string
             */
            languageIso: 'en_GB',

            /**
             * Currency which should be used for the Smart Payment Buttons
             *
             * @type string
             */
            currency: 'EUR',

            /**
             * @type string
             */
            intent: '',

            /**
             * The unique ID of the basket. Will be generated on creating the payment
             *
             * @type string
             */
            basketId: '',

            /**
             * PayPal button height small
             *
             * @type number
             */
            smallHeight: 25,

            /**
             * PayPal button height medium
             *
             * @type number
             */
            mediumHeight: 35,

            /**
             * PayPal button height large
             *
             * @type number
             */
            largeHeight: 45,

            /**
             * PayPal button height responsive
             *
             * @type number
             */
            responsiveHeight: 55,

            /**
             * PayPal button width small
             *
             * @type string
             */
            smallWidthClass: 'paypal-button-width--small',

            /**
             * PayPal button width medium
             *
             * @type string
             */
            mediumWidthClass: 'paypal-button-width--medium',

            /**
             * PayPal button width large
             *
             * @type string
             */
            largeWidthClass: 'paypal-button-width--large',

            /**
             * PayPal button width responsive
             *
             * @type string
             */
            responsiveWidthClass: 'paypal-button-width--responsive'
        },

        /**
         * PayPal Object
         */
        paypal: {},

        init: function() {
            this.applyDataAttributes();
            this.createButtonSizeObject();
            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);
            this.subscribeEvents();

            $.publish('plugin/swagPayPalUnifiedSepa/init', this);

            this.createButtons();

            $.publish('plugin/swagPayPalUnifiedSepa/buttonsCreated', this);
        },

        /**
         * Subscribes the events that are required to run this instance.
         *
         * @private
         * @method subscribeEvents
         */
        subscribeEvents: function() {
            $.subscribe(this.getEventName('plugin/swShippingPayment/onInputChanged'), $.proxy(this.createButtons, this));
        },

        createButtons: function() {
            var me = this,
                $head = $('head'),
                baseUrl = 'https://www.paypal.com/sdk/js',
                params = {
                    'client-id': this.opts.clientId,
                    intent: this.opts.intent.toLowerCase(),
                    components: 'buttons',
                    currency: this.opts.currency
                };

            if (!$head.hasClass(this.opts.scriptLoadedClass)) {
                $.ajax({
                    url: baseUrl + '?' + $.param(params, true),
                    dataType: 'script',
                    cache: true,
                    success: function() {
                        $head.addClass(me.opts.scriptLoadedClass);
                        me.paypal = window.paypal;
                        me.renderButtons();
                    }
                });
            } else {
                this.paypal = window.paypal;
                this.renderButtons();
            }
        },

        renderButtons: function() {
            var buttonConfig = this.getButtonConfig(),
                el = this.$el.get(0);

            this.paypal.Buttons(buttonConfig).render(el);
        },

        getButtonConfig: function() {
            return {
                fundingSource: this.paypal.FUNDING.SEPA,

                style: {
                    shape: this.opts.shape,
                    layout: this.opts.layout,
                    label: this.opts.label,
                    tagline: this.opts.tagline,
                    height: this.buttonSize[this.opts.size].height
                },

                /**
                 * listener for the button
                 */
                createOrder: $.proxy(this.createOrder, this),

                /**
                 * Will be called if the payment process is approved by PayPal
                 */
                onApprove: $.proxy(this.onApprove, this),

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

        createOrder: function() {
            var me = this;

            return $.ajax({
                method: 'get',
                url: me.opts.createPaymentUrl
            }).then(function(response) {
                if (response.errorUrl) {
                    window.location.replace(response.errorUrl);
                    return;
                }
                me.opts.basketId = response.basketId;

                return response.token;
            }).promise();
        },

        onApprove: function(data, actions) {
            var confirmUrl = this.opts.checkoutConfirmUrl + '?' + $.param({
                orderId: data.orderID,
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

        onPayPalAPIError: function(response) {
            $.loadingIndicator.close();

            var content = $('<div>').html(this.opts.communicationErrorMessage),
                config = {
                    title: this.opts.communicationErrorTitle,
                    width: 320,
                    height: 200
                };

            content.css('padding', '10px');

            $.modal.open(content, config);

            $.ajax({
                url: this.opts.logUrl,
                data: {
                    code: response.code,
                    message: response.message
                }
            });
        },

        /**
         * Buffer helper function to set a timeout for a function
         *
         * @param {function} fn
         * @param {number} timeout
         * @return {number}
         */
        buffer: function(fn, timeout) {
            timeout = timeout || 500;

            return window.setTimeout(fn.bind(this), timeout);
        },

        destroy: function() {
            this._destroy();
        },

        createButtonSizeObject: function () {
            this.buttonSize = {
                small: {
                    height: this.opts.smallHeight,
                    widthClass: this.opts.smallWidthClass
                },
                medium: {
                    height: this.opts.mediumHeight,
                    widthClass: this.opts.mediumWidthClass
                },
                large: {
                    height: this.opts.largeHeight,
                    widthClass: this.opts.largeWidthClass
                },
                responsive: {
                    height: this.opts.responsiveHeight,
                    widthClass: this.opts.responsiveWidthClass
                }
            };
        },
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSepa="true"]', 'swagPayPalUnifiedSepa');
})(jQuery, window);
