;(function ($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedInContextCheckout', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
            /**
             * Commit the order number to PayPal
             *
             * @type boolean
             */
            commitOrdernumber: false,

            /**
             * For possible values see: https://developer.paypal.com/sdk/js/configuration/#disable-funding
             *
             * @type string
             */
            disabledFundings: 'card,bancontact,blik,eps,giropay,ideal,mercadopago,mybank,p24,sepa,sofort,venmo',

            /**
             * For possible values see: https://developer.paypal.com/sdk/js/configuration/#enable-funding
             *
             * @type string
             */
            enabledFundings: '',

            /**
             * Show the pay later button
             *
             * @type boolean
             */
            showPayLater: false,

            /**
             * Pay later funding key
             *
             * @type string
             */
            payLaterFunding: 'paylater'
        }),

        init: function () {
            this.applyDataAttributes();
            this.buttonIsRendered = false;

            this.createOrderFunction = $.createSwagPaymentPaypalCreateOrderFunction(this.opts.createOrderUrl, this);
            this.formValidityFunctions = $.createSwagPaymentPaypalFormValidityFunctions(
                this.opts.confirmFormSelector,
                this.opts.confirmFormSubmitButtonSelector,
                this.opts.hiddenClass,
                'swagPayPalUnifiedInContextCheckout'
            );

            this.cancelPaymentFunction = $.createCancelPaymentFunction();

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.buttonSize = $.swagPayPalCreateButtonSizeObject(this.opts);

            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/init', this);

            this.createButton();

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/buttonsCreated', this);
        },

        /**
         * Creates the PayPal in-context button with the loaded PayPal javascript
         */
        createButton: function () {
            var me = this,
                $head = $('head');

            this.payPalObjectInterval = setInterval(this.payPalObjectCheck.bind(this), this.opts.interval);
            if (!$head.hasClass(this.opts.paypalScriptLoadedSelector)) {
                $.ajax({
                    url: this.renderSdkUrl(),
                    dataType: 'script',
                    cache: true,
                    success: function () {
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

        renderSdkUrl: function () {
            var enabledFundings = this.opts.enabledFundings,
                params = {
                    'client-id': this.opts.clientId,
                    'disable-funding': this.opts.disabledFundings,
                    intent: this.opts.paypalIntent.toLowerCase(),
                    commit: false
                };

            if (this.opts.showPayLater) {
                if (enabledFundings.length > 0) {
                    var tmpEnabledFundings = enabledFundings.split(',');
                    tmpEnabledFundings.push(this.opts.payLaterFunding);
                    enabledFundings = tmpEnabledFundings.join(',');
                } else {
                    enabledFundings = this.opts.payLaterFunding;
                }
            }

            if (enabledFundings.length > 0) {
                params['enable-funding'] = enabledFundings;
            }

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

        /**
         * Renders the ECS button
         */
        renderButton: function () {
            if (this.buttonIsRendered) {
                return;
            }

            this.buttonIsRendered = true;

            var buttonConfig = this.getButtonConfig(),
                el = this.$el.get(0);

            this.paypal.Buttons(buttonConfig).render(el);
        },

        /**
         * Creates the configuration for the button
         *
         * @return { Object }
         */
        getButtonConfig: function () {
            var buttonConfig = {
                style: $.swagPayPalCreateButtonStyle(this.opts, this.buttonSize, true),

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
                onError: this.createOrderFunction.onApiError.bind(this.createOrderFunction)
            };

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/createConfig', [this, buttonConfig]);

            return buttonConfig;
        },

        /**
         * @return { Promise }
         */
        onApprove: function (data, actions) {
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
        renderConfirmUrl: function (data) {
            var params = {
                token: data.orderID,
                payerId: data.payerID,
                basketId: this.opts.basketId
            };

            return $.swagPayPalRenderUrl(this.opts.returnUrl, params);
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedNormalCheckoutButtonInContext="true"]', 'swagPayPalUnifiedInContextCheckout');
})(jQuery, window);
