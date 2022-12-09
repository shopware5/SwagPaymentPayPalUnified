;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedExpressCheckoutButton', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
            /**
             * @type string
             */
            onApproveUrl: '',

            /**
             * @type string
             */
            confirmUrl: '',

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

            /**
             * @type string
             */
            communicationErrorMessage: '',

            /**
             * @type string
             */
            communicationErrorTitle: '',

            /**
             * @type string
             */
            riskManagementErrorTitle: '',

            /**
             * @type string
             */
            riskManagementErrorMessage: '',

            /**
             * PayPal button container class without PayLater
             *
             * @type string
             */
            hasNotPayLaterClass: 'paypal-unified-ec--button-placeholder',

            /**
             * PayPal button container class with PayLater
             *
             * @type string
             */
            hasPayLaterClass: 'paypal-unified-ec--button-placeholder-has-pay-later-button',

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
             * @type boolean
             */
            payLaterFunding: 'paylater',

            /**
             * Indicates that the button is on the listing page
             *
             * @type boolean
             */
            isListing: false
        }),

        /**
         * @type {Object}
         */
        expressCheckoutButton: null,

        init: function() {
            this.applyDataAttributes();
            this.applyOrderNumberDataAttribute();

            if (this.isProductExcluded()) {
                return;
            }

            this.buttonSize = $.swagPayPalCreateButtonSizeObject(this.opts);

            this.$el.addClass(this.buttonSize[this.opts.size].widthClass);

            this.createButton();

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/init', this);

            if (this.opts.buyProductDirectly) {
                $.subscribe(this.getEventName('plugin/swAjaxVariant/onRequestData'), $.proxy(this.onChangeVariant, this));
            }
        },

        applyOrderNumberDataAttribute: function() {
            this.opts.productNumber = this.$el.attr('data-productNumber');
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
            var enabledFundings = this.opts.enabledFundings,
                params = {
                    'client-id': clientId,
                    intent: this.opts.paypalIntent.toLowerCase(),
                    'disable-funding': this.opts.disabledFundings
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

            if (currency) {
                params.currency = currency;
            }

            return $.swagPayPalRenderUrl(this.opts.sdkUrl, params);
        },

        /**
         * Renders the ECS button
         */
        renderButton: function() {
            var me = this;

            if (this.opts.isListing && this.opts.showPayLater) {
                $('.' + this.opts.hasNotPayLaterClass).removeClass(this.opts.hasNotPayLaterClass).addClass(this.opts.hasPayLaterClass);
            }

            // wait for the PayPal javascript to be loaded
            me.buffer(function() {
                me.expressCheckoutButton = paypal
                    .Buttons(me.createPayPalButtonConfiguration())
                    .render(me.$el.get(0));

                $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createButton', [me, me.expressCheckoutButton]);
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
            var me = this;

            timeout = timeout || 100;

            return window.setTimeout(fn.bind(me), timeout);
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
                style: $.swagPayPalCreateButtonStyle(this.opts, this.buttonSize, true),

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
                if (response.riskManagementFailed === true) {
                    me.isRiskManagementError = true;

                    return;
                }

                return response.token;
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
                var params = {
                    expressCheckout: response.expressCheckout,
                    token: response.token
                };

                actions.redirect($.swagPayPalRenderUrl(me.opts.confirmUrl, params));
            }, function() {
                me.onPayPalAPIError();
            }).promise();
        },

        onCancel: function() {
            $.loadingIndicator.close();
        },

        onPayPalAPIError: function() {
            var content,
                config;

            if (this.isRiskManagementError) {
                this.isRiskManagementError = false;
                content = $('<div>').html(this.opts.riskManagementErrorMessage);
                config = {
                    title: this.opts.riskManagementErrorTitle,
                    width: 400,
                    height: 200
                };
            } else {
                content = $('<div>').html(this.opts.communicationErrorMessage);
                config = {
                    title: this.opts.communicationErrorTitle,
                    width: 320,
                    height: 200
                };
            }

            content.css('padding', '10px');

            $.loadingIndicator.close();

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
