;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedSmartPaymentButtons', {
        defaults: {
            /**
             * Determines whether or not only the marks are needed on the current page
             *
             * @type boolean
             */
            marksOnly: false,

            /**
             * The URL used to create the payment
             *
             * @type string
             */
            createPaymentUrl: '',

            /**
             * After approval, redirect to this URL
             *
             * @type string
             */
            checkoutConfirmUrl: '',

            /**
             * The class name to identify whether or not the paypal sdk has been loaded
             *
             * @type string
             */
            scriptLoadedClass: 'paypal-unified--paypal-sdk-loaded',

            /**
             * Holds the client id
             *
             * @type string
             */
            clientId: '',

            /**
             * The language ISO (ISO_639) for the Smart Payment Buttons.
             *
             * @type string
             */
            languageIso: 'en_GB',

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
            basketId: ''
        },

        /**
         * PayPal Object
         */
        paypal: {},

        init: function() {
            var me = this;

            me.applyDataAttributes();
            me.subscribeEvents();
            $.publish('plugin/swagPayPalUnifiedSmartPaymentButtons/init', me);

            me.createButtons();

            $.publish('plugin/swagPayPalUnifiedSmartPaymentButtons/buttonsCreated', me);
        },

        /**
         * Subscribes the events that are required to run this instance.
         *
         * @private
         * @method subscribeEvents
         */
        subscribeEvents: function() {
            var me = this;

            $.subscribe(me.getEventName('plugin/swShippingPayment/onInputChanged'), $.proxy(me.createButtons, me));
        },

        createButtons: function() {
            var me = this,
                baseUrl = 'https://www.paypal.com/sdk/js?client-id=',
                scriptUrl = baseUrl + me.opts.clientId + '&currency=' + me.opts.currency + '&components=buttons,marks&commit=false',
                $head = $('head');

            /**
             * If marks only are displayed, remove unnecessary parameters
             * But still load buttons and marks so the buttons are present on the window paypal object
             */
            if (me.opts.marksOnly) {
                scriptUrl = baseUrl + me.opts.clientId + '&components=marks';
            }

            if (!$head.hasClass(me.opts.scriptLoadedClass)) {
                $.ajax({
                    url: scriptUrl,
                    dataType: 'script',
                    cache: true,
                    success: function() {
                        $head.addClass(me.opts.scriptLoadedClass);
                        me.paypal = window.paypal;
                        me.renderButtons();
                    }
                });
            } else {
                me.paypal = window.paypal;
                me.renderButtons();
            }
        },

        renderButtons: function() {
            var me = this,
                buttonConfig = me.getButtonConfig(),
                el = me.$el.get(0);

            // Render the marks for each element visible with the id spbMarksContainer
            $('[id=spbMarksContainer]:visible').each(function() {
                me.paypal.Marks().render(this);
            });

            if (me.opts.marksOnly) {
                return;
            }

            me.paypal.Buttons(buttonConfig).render(el);
        },

        getButtonConfig: function() {
            var me = this;

            return {
                style: {
                    label: 'checkout'
                },

                /**
                 * Will be called if on smarty payment button is clicked
                 */
                createOrder: me.createOrder.bind(this),

                /**
                 * Will be called if the payment process is approved by paypal
                 */
                onApprove: me.onApprove.bind(this),

                /**
                 * Will be called if the payment process is cancelled by the customer
                 */
                onCancel: me.onCancel
            };
        },

        createOrder: function() {
            var me = this;

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            return $.ajax({
                method: 'get',
                url: me.opts.createPaymentUrl
            }).then(function(response) {
                if (response.errorUrl) {
                    window.location.replace(response.errorUrl);
                    return;
                }
                me.opts.basketId = response.basketId;
                return response.token;
            }).promise();
        },

        onApprove: function(data, actions) {
            var paymentId = data.paymentID,
                payerId = data.payerID,
                confirmUrl = this.opts.checkoutConfirmUrl + '?paymentId=' + paymentId + '&payerId=' + payerId + '&basketId=' + this.opts.basketId;

            actions.redirect(confirmUrl);
        },

        onCancel: function() {
            $.loadingIndicator.close();
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

            timeout = timeout || 500;

            return window.setTimeout(fn.bind(me), timeout);
        },

        destroy: function() {
            var me = this;
            me._destroy();
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedSmartPaymentButtons="true"]', 'swagPayPalUnifiedSmartPaymentButtons');
})(jQuery, window);
