;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPayLater', {
        defaults: {
            /**
             * label of the button
             * possible values:
             *  - buynow
             *  - checkout
             *  - credit
             *  - pay
             *
             * IMPORTANT: Changing this value can lead to legal issues!
             *
             * @type string
             */
            label: 'buynow',

            /**
             * show text under the button
             *
             * @type boolean
             */
            tagline: false,

            /**
             * size of the button
             * possible values:
             *  - small
             *  - medium
             *  - large
             *  - responsive
             *
             * @type string
             */
            size: 'medium',

            /**
             * shape of the button
             * possible values:
             *  - pill
             *  - rect
             *
             * @type string
             */
            shape: 'rect',

            /**
             * color of the button
             * possible values:
             *  - gold
             *  - blue
             *  - silver
             *  - black
             *
             * @type string
             */
            color: 'gold',

            /**
             * The language ISO (ISO_639) locale of the button.
             *
             * for possible values see: https://developer.paypal.com/api/rest/reference/locale-codes/
             *
             * @type string
             */
            locale: '',

            /**
             *  @type string
             */
            layout: 'horizontal',

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
             * The selector for the indicator whether the PayPal javascript is already loaded or not
             *
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * selector for the checkout confirm TOS element
             *
             * @type string
             */
            agbCheckboxSelector: '#sAGB',

            /**
             * This page will be opened when the payment creation fails.
             *
             * @type string
             */
            paypalErrorPage: '',

            /**
             * This page will be opened when the payment creation fails.
             *
             * @type string
             */
            currency: 'EUR',

            /**
             * The PayPal clientId
             *
             * @type string|null
             */
            clientId: null,

            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

            /**
             * @type string
             */
            createOrderUrl: '',

            /**
             * @type string
             */
            returnUrl: '',

            /**
             * Use PayPal debug mode
             *
             * @type boolean
             */
            useDebugMode: false,

            /**
             * @type string
             */
            paypalIntent: 'capture',

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
            responsiveWidthClass: 'paypal-button-width--responsive',

            /**
             * @type string
             */
            enabledFundings: 'paylater'
        },

        init: function() {
            this.applyDataAttributes();

            this.$form = $(this.opts.confirmFormSelector);
            this.$agbCheckbox = $(this.opts.agbCheckboxSelector);

            this.hideConfirmButton();
            this.disableConfirmButton();

            this.createButtonSizeObject();

            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);

            this.createButton();

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/init', this);
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

        /**
         * Hides the confirm button
         * It should not be removed completely from the DOM, because is used to trigger HTML5 form validation
         */
        hideConfirmButton: function() {
            this.$confirmButton = $(this.opts.confirmFormSubmitButtonSelector);
            this.$confirmButton.addClass('is--hidden');

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/hideConfirmButton', [this, this.$confirmButton]);
        },

        /**
         * Disables the submit function, because in some browsers the submit event is triggered,
         * even though the form is not valid
         */
        disableConfirmButton: function() {
            this._on(this.$form, 'submit', $.proxy(this.onConfirmCheckout, this));
        },

        /**
         * @param { Event } event
         */
        onConfirmCheckout: function(event) {
            event.preventDefault();
        },

        /**
         * Creates the PayPal in-context button with the loaded PayPal javascript
         */
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

            return [this.opts.sdkUrl, '?', $.param(params, true)].join('');
        },

        /**
         * Renders the ECS button
         */
        renderButton: function() {
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

                style: {
                    label: this.opts.label,
                    color: this.opts.color,
                    shape: this.opts.shape,
                    layout: this.opts.layout,
                    tagline: this.opts.tagline,
                    height: this.buttonSize[this.opts.size].height
                },

                /**
                 * Will be called on initialisation of the payment button
                 */
                onInit: this.onInitPayPalButton.bind(this),

                /**
                 * Will be called if the payment button is clicked
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
                url: me.opts.createOrderUrl
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

            return actions.redirect(this.renderConfirmUrl(data));
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

            return [this.opts.returnUrl, '?', params].join('');
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

            this.$agbCheckbox.on('change', function (event) {
                if (event.target.checked) {
                    actions.enable();
                } else {
                    actions.disable();
                }
            });
        },

        onPayPalButtonClick: function() {
            if (Object.prototype.hasOwnProperty.call(this.$form[0], 'checkValidity')) {
                this.$form[0].checkValidity();

                return;
            }

            if (!this.$agbCheckbox.prop('checked')) {
                $('label[for="sAGB"]').addClass('has--error');
            }
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedPayLater="true"]', 'swagPayPalUnifiedPayLater');
})(jQuery, window);
