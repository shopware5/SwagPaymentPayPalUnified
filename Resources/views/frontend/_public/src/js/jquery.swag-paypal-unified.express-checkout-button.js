;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedExpressCheckoutButton', {
        defaults: {
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
            createPaymentUrl: '',

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
             * The selector for the product number on the detail page.
             * @type string
             */
            productNumberSelector: 'input[name="sAdd"]'
        },

        /**
         * @type {Object}
         */
        expressCheckoutButton: null,

        init: function() {
            var me = this;

            me.applyDataAttributes();

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
         * Creates the paypal express checkout over the provided paypal javascript
         */
        createButton: function() {
            var me = this;

            me.expressCheckoutButton = paypal.Button.render(me.createPayPalButtonConfiguration(), me.$el.get(0));

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createButton', [me, me.expressCheckoutButton]);
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
                 * environment property of the button
                 */
                env: me.opts.paypalMode,

                /**
                 * styling of the button
                 */
                style: {
                    size: me.opts.size,
                    shape: me.opts.shape,
                    color: me.opts.color
                },

                /**
                 * listener for the button
                 */
                payment: $.proxy(me.onPayPalPayment, me),

                /**
                 * only needed for overlay solution
                 * called if the customer accepts the payment
                 */
                onAuthorize: $.noop
            };

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createConfig', [me, config]);

            return config;
        },

        /**
         * Callback method for the "payment" function of the button.
         * Calls an action which creates the payment and redirects to the paypal page.
         */
        onPayPalPayment: function() {
            var me = this,
                token,
                form;

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/beforeCreatePayment', me);

            if (CSRF.checkToken()) {
                token = CSRF.getToken();
            }

            form = me.createCreatePaymentForm(token);

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false
            });

            me.buffer(function() {
                form.submit();
            }, 100);

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createPayment', me);
        },

        /**
         * Creates the form which calls the action.
         * Appends a new form which stores further required information that are being
         * used in the action later on.
         *
         * @param {String} token
         * @return {Object}
         */
        createCreatePaymentForm: function(token) {
            var me = this,
                $form,
                createField = function(name, val) {
                    return $('<input>', {
                        type: 'hidden',
                        name: name,
                        value: val
                    });
                };

            $form = $('<form>', {
                action: me.opts.createPaymentUrl,
                method: 'POST'
            });

            createField('__csrf_token', token).appendTo($form);

            if (me.opts.detailPage) {
                createField('addProduct', true).appendTo($form);
                createField('productNumber', me.getProductNumber()).appendTo($form);
                createField('productQuantity', me.getProductQuantity()).appendTo($form);
            }

            $.publish('plugin/swagPayPalUnifiedExpressCheckoutButtonCart/createRequestForm', [me, $form]);

            $form.appendTo($('body'));

            return $form;
        },

        /**
         * Helper function that returns the current product number.
         * Will only be used on the product detail page
         *
         * @returns {String}
         */
        getProductNumber: function() {
            var me = this;

            return $(me.opts.productNumberSelector).val();
        },

        /**
         * Helper function that returns the current product quantity.
         * Will only be used on the product detail page.
         *
         * @returns {Number}
         */
        getProductQuantity: function() {
            var me = this;

            return $(me.opts.productQuantitySelector).val();
        },

        /**
         * Buffer for submitting the form
         * If we don't delay the call, the loading indicator will not show up on mobile
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

    window.StateManager.addPlugin('*[data-paypalUnifiedEcButton="true"]', 'swagPayPalUnifiedExpressCheckoutButton');
})(jQuery, window);
