;(function($, window, paypal) {
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
             * selector of the dom element which contains the json encoded cart data
             */
            cartDataSelector: '.paypal-unified-ec--cart-data',

            /**
             * size of the button
             * possible values:
             *  - tiny
             *  - small
             *  - medium
             */
            size: 'medium',

            /**
             * shape of the button
             * possible values:
             *  - pill
             *  - rect
             */
            shape: 'rect',

            /**
             * size of the button
             * possible values:
             *  - gold
             *  - blue
             *  - silver
             */
            color: 'gold'
        },

        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.createButton();
        },

        /**
         * creates the paypal express checkout over the provided paypal javascript
         */
        createButton: function() {
            var me = this;

            me.expressCheckoutButton = paypal.Button.render(me.createPayPalButtonConfiguration(), me.$el.get(0));
        },

        /**
         * creates the configuration for the button
         *
         * @return {Object}
         */
        createPayPalButtonConfiguration: function() {
            var me = this;

            return {
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
        },

        /**
         * callback method for the "payment" function of the button
         * calls an action which creates the payment and redirects to the paypal page
         *
         * @return {boolean}
         */
        onPayPalPayment: function() {
            var me = this,
                token,
                cartData,
                form;

            if (CSRF.checkToken()) {
                token = CSRF.getToken();
            }

            cartData = $(me.opts.cartDataSelector).html();
            form = me.createCreatePaymentForm(cartData, token);

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false
            });

            me.buffer(function() {
                form.submit();
            }, 100);

            return true;
        },

        /**
         * creates the form which calls the action
         *
         * @param {String} cartData
         * @param {String} token
         * @return {Object}
         */
        createCreatePaymentForm: function(cartData, token) {
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

            createField('cartData', cartData).appendTo($form);
            createField('__csrf_token', token).appendTo($form);

            $form.appendTo($('body'));

            return $form;
        },

        /**
         * buffer for submitting the form
         * if we don't delay the call, the loading indicator will not show up on mobile
         *
         * @param {function} fn
         * @param {number} timeout
         * @return {number}
         */
        buffer: function(fn, timeout) {
            var me = this;

            timeout = timeout || 100;

            return window.setTimeout(fn.bind(me), timeout);
        }
    });

    window.StateManager.addPlugin('.paypal-unified-ec--button-container', 'swagPayPalUnifiedExpressCheckoutButton');
})(jQuery, window, paypal);
