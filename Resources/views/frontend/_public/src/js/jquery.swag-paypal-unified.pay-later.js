;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPayLater', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
            /**
             * @type string
             */
            enabledFundings: 'paylater'
        }),

        init: function() {
            this.applyDataAttributes();
            this.buttonIsRendered = false;

            this.createOrderFunction = $.createSwagPaymentPaypalCreateOrderFunction(this.opts.createOrderUrl, this);
            this.formValidityFunctions = $.createSwagPaymentPaypalFormValidityFunctions(
                this.opts.confirmFormSelector,
                this.opts.confirmFormSubmitButtonSelector,
                this.opts.hiddenClass,
                'swagPayPalUnifiedPayLater'
            );

            this.cancelPaymentFunction = $.createCancelPaymentFunction();

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.buttonSize = $.swagPayPalCreateButtonSizeObject(this.opts);

            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);

            this.createButton();

            $.publish('plugin/swagPayPalUnifiedPayLater/init', this);
        },

        /**
         * Creates the PayPal in-context button with the loaded PayPal javascript
         */
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
                'enable-funding': this.opts.enabledFundings,
                intent: this.opts.paypalIntent.toLowerCase()
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

        /**
         * Renders the ECS button
         */
        renderButton: function() {
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
        getButtonConfig: function() {
            var buttonConfig = {
                fundingSource: 'paylater',

                style: Object.assign(
                    $.swagPayPalCreateButtonStyle(this.opts, this.buttonSize, false),
                    {
                        color: 'gold'
                    }
                ),

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

            $.publish('plugin/swagPayPalUnifiedPayLater/createConfig', [this, buttonConfig]);

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

            return actions.redirect(this.renderConfirmUrl(data));
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

            return $.swagPayPalRenderUrl(this.opts.returnUrl, params);
        },

        onPayPalAPIError: function() {
            window.location.replace(this.opts.paypalErrorPage);
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedPayLater="true"]', 'swagPayPalUnifiedPayLater');
})(jQuery, window);
