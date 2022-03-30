;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedExpressCheckoutButton', {
        defaults: {
            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

            currency: '',

            /**
             * The API client-ID identifying a merchant.
             *
             * @type string
             */
            clientId: '',

            /**
             * Use PayPal debug mode
             *
             * @type boolean
             */
            useDebugMode: false,

            /**
             * URL used to create a new payment
             *
             * @type string
             */
            createOrderUrl: '',

            /**
             * @type string
             */
            onApproveUrl: '',

            /**
             * @type string
             */
            confirmUrl: '',

            /**
             * size of the button
             * possible values:
             *  - small
             *  - medium
             *  - large
             *  - responsive
             *
             *  @type string
             */
            size: 'medium',

            /**
             * shape of the button
             * possible values:
             *  - pill
             *  - rect
             *
             *  @type string
             */
            shape: 'rect',

            /**
             * size of the button
             * possible values:
             *  - gold
             *  - blue
             *  - silver
             *  - black
             *
             *  @type string
             */
            color: 'gold',

            /**
             * show text under the button
             *
             * @type boolean
             */
            tagline: false,

            /**
             * Button label: https://developer.paypal.com/docs/business/checkout/reference/style-guide/#label
             *
             *  @type string
             */
            label: 'checkout',

            /**
             * Button layout: https://developer.paypal.com/docs/business/checkout/reference/style-guide/#layout
             *
             *  @type string
             */
            layout: 'horizontal',

            /**
             * The language ISO (ISO_639) locale of the button.
             *
             * for possible values see: https://developer.paypal.com/api/rest/reference/locale-codes/
             *
             * @type string
             */
            locale: '',

            /**
             * A boolean indicating if the current page is a product detail page.
             *
             * @type boolean
             */
            buyProductDirectly: false,

            /**
             * The selector for the quantity selection on the detail page.
             *
             * @type string
             */
            productQuantitySelector: '#sQuantity',

            /**
             * The product number which should be added to the cart.
             *
             * @type string|null
             */
            productNumber: null,

            /**
             * The selector for the indicator whether the PayPal javascript is already loaded or not
             *
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * Excluded products by the risk management.
             *
             * @type array|null
             */
            riskManagementMatchedProducts: null,

            /**
             * @type string
             */
            paypalIntent: 'capture',

            /**
             * Excluded esd products.
             *
             * @type array|null
             */
            esdProducts: null,

            /**
             * @type string
             */
            communicationErrorMessage: '',

            /**
             * @type string
             */
            communicationErrorTitle: '',

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
         * @type {Object}
         */
        expressCheckoutButton: null,

        init: function() {
            var me = this;

            me.applyDataAttributes();

            if (me.isProductExcluded()) {
                return;
            }

            this.createButtonSizeObject();
            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);

            me.createButton();

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/init', me);

            if (me.opts.buyProductDirectly) {
                $.subscribe(me.getEventName('plugin/swAjaxVariant/onRequestData'), $.proxy(me.onChangeVariant, me));
            }
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
         * Will be triggered when the selected variant was changed.
         * Re-initializes this plugin.
         */
        onChangeVariant: function() {
            window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
        },

        /**
         * Checks if the current product excluded by the risk management
         *
         * @return {boolean}
         */
        isProductExcluded: function() {
            var me = this,
                productNumber = me.opts.productNumber,
                excludedProductNumbers,
                riskManagementMatchedProducts = [],
                esdProducts = [];

            if (productNumber === null || productNumber === '') {
                return false;
            }

            if (me.opts.riskManagementMatchedProducts !== '') {
                riskManagementMatchedProducts = me.opts.riskManagementMatchedProducts;
            }

            if (me.opts.esdProducts !== '') {
                esdProducts = me.opts.esdProducts;
            }

            excludedProductNumbers = [].concat(
                riskManagementMatchedProducts,
                esdProducts
            );

            return $.inArray(productNumber, excludedProductNumbers) >= 0;
        },

        /**
         * Creates the PayPal express checkout button with the loaded PayPal javascript
         */
        createButton: function() {
            var me = this,
                $head = $('head');

            if (!$head.data(me.opts.paypalScriptLoadedSelector)) {
                $.ajax({
                    url: me.renderSdkUrl(me.opts.clientId, me.opts.currency),
                    dataType: 'script',
                    cache: true,
                    success: function() {
                        $head.data(me.opts.paypalScriptLoadedSelector, true);
                        me.renderButton();
                    }
                });
            } else {
                me.renderButton();
            }
        },

        renderSdkUrl: function(clientId, currency) {
            var params = {
                'client-id': clientId,
                intent: this.opts.paypalIntent.toLowerCase()
            };

            if (this.opts.locale.length > 0) {
                params.locale = this.opts.locale;
            }

            if (this.opts.useDebugMode) {
                params.debug = true;
            }

            if (currency) {
                params.currency = currency;
            }

            return [this.opts.sdkUrl, '?', $.param(params, true)].join('');
        },

        /**
         * Renders the ECS button
         */
        renderButton: function() {
            var me = this;

            // wait for the PayPal javascript to be loaded
            me.buffer(function() {
                me.expressCheckoutButton = paypal
                    .Buttons(me.createPayPalButtonConfiguration())
                    .render(me.$el.get(0));

                $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createButton', [me, me.expressCheckoutButton]);
            });
        },

        /**
         * Creates the configuration for the button
         *
         * @return {Object}
         */
        createPayPalButtonConfiguration: function() {
            var me = this,
                config;

            config = {
                /**
                 * styling of the button
                 */
                style: {
                    shape: me.opts.shape,
                    color: me.opts.color,
                    layout: me.opts.layout,
                    label: me.opts.label,
                    tagline: me.opts.tagline,
                    height: this.buttonSize[this.opts.size].height
                },

                /**
                 * listener for the button
                 */
                createOrder: $.proxy(me.createOrder, me),

                /**
                 * Will be called if the payment process is approved by PayPal
                 */
                onApprove: $.proxy(me.onApprove, me),

                /**
                 * Will be called if the payment process is cancelled by the customer
                 */
                onCancel: this.onCancel.bind(this),

                /**
                 * Will be called if any api error occurred
                 */
                onError: this.onPayPalAPIError.bind(this)
            };

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createConfig', [me, config]);

            return config;
        },

        /**
         * This method dispatches a request to the `\Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout`-controller (default)
         * which initialises an order at PayPal.
         */
        createOrder: function() {
            var me = this,
                data = {};

            $.loadingIndicator.open({
                closeOnClick: false,
                delay: 100
            });

            if (me.opts.buyProductDirectly) {
                data = {
                    addProduct: true,
                    productNumber: me.opts.productNumber,
                    productQuantity: $(me.opts.productQuantitySelector).val()
                };
            }

            return $.ajax({
                url: me.opts.createOrderUrl,
                data: data
            }).then(function(response) {
                return response.paypalOrderId;
            }, function() {
            }).promise();
        },

        /**
         * This method dispatches a request to the `\Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout`-controller (default)
         * which creates a new customer account and also logs the user in.
         */
        onApprove: function(data, actions) {
            var me = this;

            return $.ajax({
                url: me.opts.onApproveUrl,
                data: data
            }).then(function(response) {
                var url = me.opts.confirmUrl + '?' + $.param({
                    expressCheckout: response.expressCheckout,
                    paypalOrderId: response.paypalOrderId
                });

                actions.redirect(url);
            }, function() {
                me.onPayPalAPIError();
            }).promise();
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        onPayPalAPIError: function() {
            $.loadingIndicator.close();

            var content = $('<div>').html(this.opts.communicationErrorMessage),
                config = {
                    title: this.opts.communicationErrorTitle,
                    width: 320,
                    height: 200
                };

            content.css('padding', '10px');

            $.modal.open(content, config);
        },

        /**
         * Helper function that returns the current product quantity.
         * Will only be used on the product detail page.
         *
         * @returns {Number}
         */
        getProductQuantity: function() {
            var me = this,
                quantity = $(me.opts.productQuantitySelector).val();

            if (quantity === undefined) {
                return 1;
            }

            return quantity;
        },

        /**
         * Buffer helper function to set a timeout for a function
         *
         * @param {function} fn
         * @param {number} timeout
         * @return {number}
         */
        buffer: function(fn, timeout) {
            var me = this;

            timeout = timeout || 100;

            return window.setTimeout(fn.bind(me), timeout);
        },

        /**
         * Destroys the plugin and unsubscribes from subscribed events
         */
        destroy: function() {
            var me = this;

            $.unsubscribe(me.getEventName('plugin/swAjaxVariant/onRequestData'));

            me._destroy();
        }
    });

    /**
     *  After the loading another product variant, we lose the
     *  plugin instance, therefore, we have to re-initialize it here.
     */
    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
    });

    $.subscribe('plugin/swInfiniteScrolling/onFetchNewPageFinished', function() {
        window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
    });

    $.subscribe('plugin/swInfiniteScrolling/onLoadPreviousFinished', function() {
        window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
})(jQuery, window);
