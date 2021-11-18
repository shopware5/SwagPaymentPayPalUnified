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
             * Depending on the mode, the library will load the PSP from different locations. live will
             * load it from paypal.com whereas sandbox will load it from sandbox.paypal.com. The
             * library will also emit warning to the console if the mode is sandbox (in live mode it will
             * do so only for required parameters).
             *
             * Available modes:
             *  - production
             *  - sandbox
             *
             * @type string
             */
            paypalMode: 'production',

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
             * The language ISO (ISO_639) for the payment wall.
             *
             * @type string
             */
            paypalLanguage: 'en_US',

            /**
             * A boolean indicating if the current page is an product detail page.
             *
             * @type boolean
             */
            detailPage: false,

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
             * Excluded esd products.
             *
             * @type array|null
             */
            esdProducts: null,
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

            me.createButton();

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/init', me);

            if (me.opts.detailPage) {
                $.subscribe(me.getEventName('plugin/swAjaxVariant/onRequestData'), $.proxy(me.onChangeVariant, me));
            }
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
                productNumbers = [].concat(
                    me.opts.riskManagementMatchedProducts,
                    me.opts.esdProducts
                );

            if (Array.isArray(productNumbers) && $.inArray(me.opts.productNumber, productNumbers) >= 0) {
                return true;
            }

            return false;
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
            var me = this,
                params = {};

            if (clientId) {
                params['client-id'] = clientId;
            } else {
                params['client-id'] = 'sb';
            }

            if (currency) {
                params.currency = currency;
            }

            if (me.opts.paypalMode === 'sandbox') {
                params.debug = true;
            }

            if (params.length < 1) {
                return me.opts.sdkUrl;
            }

            return me.opts.sdkUrl + '?' + $.param(params, true);
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
                    size: me.opts.size,
                    shape: me.opts.shape,
                    color: me.opts.color,
                    layout: me.opts.layout,
                    label: me.opts.label,
                    tagline: me.opts.tagline,
                },

                /**
                 * listener for the button
                 */
                createOrder: $.proxy(me.createOrder, me),

                onApprove: $.proxy(me.onApprove, me),
            };

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createConfig', [me, config]);

            return config;
        },

        /**
         * This method dispatches a request to the `\Shopware_Controllers_Widgets_PaypalUnifiedV2ExpressCheckout`-controller (default)
         * which initialises an order at PayPal.
         */
        createOrder: function(data, actions) {
            var me = this;

            $.loadingIndicator.open({
                closeOnClick: false,
                delay: 100
            });

            return $.ajax({
                url: me.opts.createOrderUrl,
                data: {
                    addProduct: true,
                    productNumber: me.opts.productNumber,
                    productQuantity: $(me.opts.productQuantitySelector).val(),
                },
            }).then(function(response) {
                return response.orderId;
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
                data: data,
            }).then(function(response) {
                var url = me.opts.confirmUrl + '?' + $.param({
                    expressCheckout: response.expressCheckout,
                    orderId: response.orderId,
                });

                actions.redirect(url);
            }).promise();
        },

        /**
         * Helper function that returns the current product quantity.
         * Will only be used on the product detail page.
         *
         * @returns {Number}
         */
        getProductQuantity: function() {
            var me = this,
                quantity = $(me.opts.productQuantitySelector).val()

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
