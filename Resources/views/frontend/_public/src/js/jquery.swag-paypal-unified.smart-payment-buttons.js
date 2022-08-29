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
            returnUrl: '',

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
             * Use PayPal debug mode
             *
             * @type boolean
             */
            useDebugMode: false,

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
            basketId: '',

            /**
             * PayPal button label
             *
             * IMPORTANT: Changing this value can lead to legal issues!
             *
             * @type string
             */
            label: 'buynow',

            /**
             *  @type string
             */
            hiddenClass: 'is--hidden'
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
                'swagPayPalUnifiedSmartPaymentButtons'
            );

            this.formValidityFunctions.hideConfirmButton();
            this.formValidityFunctions.disableConfirmButton();

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
                params.components = 'funding-eligibility,marks';
            } else {
                params.components = 'marks,buttons';
                params.commit = false;
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
                style: {
                    label: this.opts.label
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

        onPayPalAPIError: function() {
            window.location.replace(this.opts.paypalErrorPage);
        },

        destroy: function() {
            this._destroy();
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSmartPaymentButtons="true"]', 'swagPayPalUnifiedSmartPaymentButtons');
})(jQuery, window);
