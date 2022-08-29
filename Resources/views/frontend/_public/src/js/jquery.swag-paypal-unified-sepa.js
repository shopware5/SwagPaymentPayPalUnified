;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedSepa', {
        defaults: {
            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

            /**
             * Use PayPal debug mode
             *
             * @type boolean
             */
            useDebugMode: false,

            /**
             * The URL used to create the order
             *
             * @type string
             */
            createOrderUrl: '',

            /**
             * @type string
             */
            paypalErrorPageUrl: '',

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
             * After approval, redirect to this URL
             *
             * @type string
             */
            returnUrl: '',

            /**
             * The class name to identify whether or not the paypal sdk has been loaded
             *
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * Holds the client id
             *
             * @type string
             */
            clientId: '',

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
             * selector for the checkout confirm form element
             *
             * @type string
             */
            confirmFormSelector: '#confirm--form',

            /**
             * selector for the submit button of the checkout confirm form
             *
             * @type string
             */
            confirmFormSubmitButtonSelector: ':submit[form="confirm--form"]',

            /**
             *  @type string
             */
            hiddenClass: 'is--hidden',

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
            responsiveWidthClass: 'paypal-button-width--responsive',

            /**
             * PayPal button label
             *
             * IMPORTANT: Changing this value can lead to legal issues!
             *
             * @type string
             */
            label: 'buynow'
        },

        /**
         * PayPal Object
         */
        paypal: {},

        init: function() {
            this.applyDataAttributes();

            this.createOrderFunction = $.createSwagPaymentPaypalCreateOrderFunction(this.opts.createOrderUrl, this);
            this.formValidityFunctions = $.createSwagPaymentPaypalFormValidityFunctions(
                this.opts.confirmFormSelector,
                this.opts.confirmFormSubmitButtonSelector,
                this.opts.hiddenClass,
                'swagPayPalUnifiedSepa'
            );

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.createButtonSizeObject();
            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);
            this.subscribeEvents();

            $.publish('plugin/swagPayPalUnifiedSepa/init', this);

            this.createButton();

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

        createButton: function() {
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
                        me.renderButton();
                    }
                });
            } else {
                this.paypal = window.paypal;
                this.renderButton();
            }
        },

        renderSdkUrl: function() {
            var params = {
                'client-id': this.opts.clientId,
                intent: this.opts.intent.toLowerCase(),
                components: 'buttons,funding-eligibility'
            };

            if (this.opts.locale.length > 0) {
                params.locale = this.opts.locale;
            }

            if (this.opts.useDebugMode) {
                params.debug = true;
            }

            if (this.opts.currency) {
                params.currency = this.opts.currency;
            }

            return $.swagPayPalRenderUrl(this.opts.sdkUrl, params);
        },

        renderButton: function() {
            var buttonConfig = this.getButtonConfig(),
                el = this.$el.get(0);

            if (!this.paypal.isFundingEligible(this.paypal.FUNDING.SEPA)) {
                this.onPayPalAPIError({ sepaIsNotEligible: true });
                return;
            }

            this.paypal.Buttons(buttonConfig).render(el);
        },

        getButtonConfig: function() {
            return {
                fundingSource: this.paypal.FUNDING.SEPA,

                style: {
                    shape: this.opts.shape,
                    layout: this.opts.layout,
                    label: this.opts.label,
                    height: this.buttonSize[this.opts.size].height
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
                 * listener for the button
                 */
                createOrder: this.createOrderFunction.createOrder.bind(this.createOrderFunction),

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

        onApprove: function(data, actions) {
            var params = {
                paypalOrderId: data.orderID,
                payerId: data.payerID,
                basketId: this.opts.basketId
            };

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            actions.redirect($.swagPayPalRenderUrl(this.opts.returnUrl, params));
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        createButtonSizeObject: function() {
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

        /**
         * @param { object|null } extraParams
         */
        onPayPalAPIError: function(extraParams) {
            if (extraParams === null) {
                extraParams = {};
            }

            window.location.replace($.swagPayPalRenderUrl(this.opts.paypalErrorPageUrl, extraParams));
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSepa="true"]', 'swagPayPalUnifiedSepa');
})(jQuery, window);
