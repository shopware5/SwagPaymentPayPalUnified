;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedSmartPaymentButtons', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
            /**
             * Determines whether only the marks are needed on the current page
             *
             * @type boolean
             */
            marksOnly: false,

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
                'swagPayPalUnifiedSmartPaymentButtons'
            );

            this.cancelPaymentFunction = $.createCancelPaymentFunction();

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

            this.buttonSize = $.swagPayPalCreateButtonSizeObject(this.opts);

            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);

            this.subscribeEvents();
            $.publish('plugin/swagPayPalUnifiedSmartPaymentButtons/init', this);

            this.createButtons();

            $.publish('plugin/swagPayPalUnifiedSmartPaymentButtons/buttonsCreated', this);
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
                        me.renderButtons();
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
            this.renderButtons();
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
                params.components = 'funding-eligibility,marks';
            } else {
                params.components = 'funding-eligibility,marks,buttons';
                params.commit = true;
                params.currency = this.opts.currency;
            }

            if (this.opts.locale.length > 0) {
                params.locale = this.opts.locale;
            }

            if (this.opts.useDebugMode) {
                params.debug = true;
            }

            return $.swagPayPalRenderUrl(this.opts.sdkUrl, params);
        },

        renderButtons: function() {
            if (this.buttonIsRendered) {
                return;
            }

            this.buttonIsRendered = true;

            var me = this,
                buttonConfig = this.getButtonConfig(),
                el = this.$el.get(0);

            // Render the marks for each element visible with the id spbMarksContainer
            $('[id=spbMarksContainer]:visible').each(function() {
                me.paypal.Marks().render(this);
            });

            if (this.opts.marksOnly) {
                return;
            }

            this.paypal.Buttons(buttonConfig).render(el);
        },

        getButtonConfig: function() {
            return {
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
                 * Will be called if on smarty payment button is clicked
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
        },

        onApprove: function(data, actions) {
            var params = {
                token: data.orderID,
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

        destroy: function() {
            this._destroy();
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSmartPaymentButtons="true"]', 'swagPayPalUnifiedSmartPaymentButtons');
})(jQuery, window);
