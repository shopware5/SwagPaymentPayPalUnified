;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedSepa', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
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
             * After approval, redirect to this URL
             *
             * @type string
             */
            returnUrl: '',

            /**
             * The unique ID of the basket. Will be generated on creating the payment
             *
             * @type string
             */
            basketId: ''
        }),

        /**
         * PayPal Object
         */
        paypal: {},

        init: function() {
            this.applyDataAttributes();
            this.buttonIsRendered = false;

            this.createOrderFunction = $.createSwagPaymentPaypalCreateOrderFunction(this.opts.createOrderUrl, this);
            this.formValidityFunctions = $.createSwagPaymentPaypalFormValidityFunctions(
                this.opts.confirmFormSelector,
                this.opts.confirmFormSubmitButtonSelector,
                this.opts.hiddenClass,
                'swagPayPalUnifiedSepa'
            );

            this.cancelPaymentFunction = $.createCancelPaymentFunction();

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.buttonSize = $.swagPayPalCreateButtonSizeObject(this.opts);

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

            this.payPalObjectInterval = setInterval(this.payPalObjectCheck.bind(this), this.opts.interval);
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
            }
        },

        payPalObjectCheck: function () {
            if (window.paypal === undefined || window.paypal === null || typeof window.paypal.Buttons !== 'function') {
                return;
            }

            clearInterval(this.payPalObjectInterval);
            this.paypal = window.paypal;
            this.renderButton();
        },

        renderSdkUrl: function() {
            var params = {
                'client-id': this.opts.clientId,
                intent: this.opts.paypalIntent.toLowerCase(),
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
            if (this.buttonIsRendered) {
                return;
            }

            this.buttonIsRendered = true;

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
                onCancel: this.cancelPaymentFunction.onCancel.bind(this.cancelPaymentFunction),

                /**
                 * Will be called if any api error occurred
                 */
                onError: this.onPayPalAPIError.bind(this)
            };
        },

        onApprove: function(data, actions) {
            var params = {
                    token: data.orderID,
                    payerId: data.payerID,
                    basketId: this.opts.basketId
                },
                url = $.swagPayPalRenderUrl(this.opts.returnUrl, params);

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            $.redirectToUrl(url);
        },

        /**
         * @param { object|null } extraParams
         */
        onPayPalAPIError: function(extraParams) {
            if (extraParams === null) {
                extraParams = {};
            }

            var url = $.swagPayPalRenderUrl(this.opts.paypalErrorPageUrl, extraParams);
            $.redirectToUrl(url);
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSepa="true"]', 'swagPayPalUnifiedSepa');
})(jQuery, window);
