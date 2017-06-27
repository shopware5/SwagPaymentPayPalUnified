;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedInContextCheckout', {
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
             * label of the button
             * possible values:
             *  - checkout
             *  - credit
             *  - pay
             *
             * @type string
             */
            label: 'checkout',

            /**
             * size of the button
             * possible values:
             *  - tiny
             *  - small
             *  - medium
             *  - large
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
             *
             * @type string
             */
            color: 'gold',

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
            confirmFormSubmitButtonSelector: ':submit[form="confirm--form"]'
        },

        /**
         * @type {Object}
         */
        inContextCheckoutButton: null,

        init: function() {
            var me = this;

            me.applyDataAttributes();

            me.$form = $(me.opts.confirmFormSelector);
            me.hideConfirmButton();
            me.createButton();

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/init', me);
        },

        /**
         * Hides the confirm button
         * It should not be removed completely from the DOM, because is used to trigger HTML5 form validation
         */
        hideConfirmButton: function() {
            var me = this;

            me.$confirmButton = $(me.opts.confirmFormSubmitButtonSelector);
            me.$confirmButton.addClass('is--hidden');

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/hideConfirmButton', [me, me.$confirmButton]);
        },

        /**
         * Creates the paypal express checkout over the provided paypal javascript
         */
        createButton: function() {
            var me = this;

            me.inContextCheckoutButton = paypal.Button.render(me.createPayPalButtonConfiguration(), me.$el.get(0));

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/createButton', [me, me.inContextCheckoutButton]);
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
                    label: me.opts.label,
                    size: me.opts.size,
                    shape: me.opts.shape,
                    color: me.opts.color
                },

                /**
                 * listener for custom validations
                 */
                validate: $.proxy(me.onValidate, me),

                /**
                 * on click listener for the button
                 */
                onClick: $.proxy(me.onButtonClick, me),

                /**
                 * listener for the button
                 */
                payment: $.proxy(me.onPayPalPayment, me),

                /**
                 * only needed for overlay solution
                 * called if the customer accepts the payment
                 */
                onAuthorize: $.proxy(me.onPayPalAuthorize, me)
            };

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/createConfig', [me, config]);

            return config;
        },

        /**
         * adds a listener to the checkout confirm form to check its validity
         * enables or disables the paypal checkout button
         *
         * @param {Object} actions
         */
        onValidate: function(actions) {
            var me = this;

            me.$form.on('change', function() {
                if (me.checkFormValidity(actions)) {
                    $.publish('plugin/swagPayPalUnifiedInContextCheckout/formValid', [me, actions]);

                    return actions.enable();
                }

                $.publish('plugin/swagPayPalUnifiedInContextCheckout/formInValid', [me, actions]);

                return actions.disable();
            });

            if (!me.checkFormValidity(actions)) {
                $.publish('plugin/swagPayPalUnifiedInContextCheckout/formInValid', [me, actions]);

                return actions.disable();
            }
        },

        /**
         * validates the checkout confirm form
         *
         * @return {boolean}
         */
        checkFormValidity: function() {
            var me = this,
                form = me.$form.get(0),
                isFormValid = form.checkValidity();

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/checkFormValidity', [me, isFormValid, me.$form]);

            return isFormValid;
        },

        /**
         * called if the paypal button is clicked
         * clicks the hidden confirm button to trigger the HTML5 validation of the checkout confirm form
         */
        onButtonClick: function() {
            var me = this;

            if (!me.checkFormValidity()) {
                me.$confirmButton.trigger('click');
            }
        },

        /**
         * Callback method for the "payment" function of the button.
         * Calls an action which creates the payment and redirects to the paypal page.
         *
         * @return {string}
         */
        onPayPalPayment: function() {
            var me = this;

            $('<input>', {
                type: 'hidden',
                name: 'useInContext',
                value: true
            }).appendTo(me.$form);

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/beforeCreatePayment', [me, me.$form]);

            return me.processPayment().then(function(response) {
                $.publish('plugin/swagPayPalUnifiedInContextCheckout/paymentCreated', [me, response]);
                return response.paymentId;
            });
        },

        /**
         * calls the checkout confirm form URL with ajax and returns it as promise
         * With this solution the Shopware logic is not avoided
         *
         * @return {Object}
         */
        processPayment: function() {
            var me = this,
                url = me.$form.attr('action'),
                method = me.$form.attr('method'),
                data = me.$form.serialize();

            $.publish('plugin/swagPayPalUnifiedInContextCheckout/beforeAjax', [me, url, method, data]);

            return $.ajax({
                url: url,
                method: method,
                data: data
            }).promise();
        },

        /**
         * Callback method for the "authorize" function of the button.
         * Directly redirects to the given return URL
         *
         * @param {Object} data
         * @param {Object} actions
         * @return {Object}
         */
        onPayPalAuthorize: function(data, actions) {
            return actions.redirect();
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedNormalCheckoutButtonInContext="true"]', 'swagPayPalUnifiedInContextCheckout');
})(jQuery, window);
