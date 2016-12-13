;(function($, window) {
    'use strict';

    $.plugin('PayPalUnifiedPaymentWall', {

        /** @object Default plugin configuration */
        defaults: {
            /** @string placeholder for the iFrame request */
            placeHolder: 'ppplus',
            /** @string default location of the buttons in the iFrame */
            buttonLocation: 'outside',
            /** @string default action of the iFrame */
            userAction: 'commit',
            /** @string default approval url */
            'paypal-unified-approval-url': '',
            /** @string default country iso */
            'paypal-unified-country-iso': '',
            /** @string default sandbox usage */
            'paypal-unified-sandbox': '',
            /** @string default selector for confirm page */
            confirmPageSelector: '#confirm--form',
            /** @string default selector for basket button */
            basketButtonSelector: '#basketButton',
            /** @string default selector for payment mean */
            paymentMeanSelector: '#payment_mean',
            /** @string default selector for confirm check */
            confirmCheckSelector: '.is--act-confirm',
            /** @string default selector for alternative basket button */
            altBasketButtonSelector: '.main--actions button[type=submit]',
            /** @string default selector for the PayPalPlus container */
            containerSelector: '#ppplus',
            /** @string default language */
            language: 'de_DE',
            /** @string default selector agb checkbox */
            agbSelector: '#sAGB'
        },

        /**
         * Initializes the plugin
         *
         * @returns void
         */
        init: function () {
            var me = this,
                paymentId = me.$el.data('paypal-unified-payment-id'),
                userPaymentId = me.$el.data('paypal-unified-user-payment-id');

            me.applyDataAttributes();
            me.deselectPayPalMethod(me.$el);

            me.paypalIsCurrentPaymentMethodPaypal = (paymentId === userPaymentId);
            me._ppp = me.createPaymentWall(paymentId);

            $.publish('plugin/PayPalUnifiedPaymentWall/init', [me, me.$parent]);
        },

        createPaymentWall: function (paymentId) {
            var me = this,
                $basketButton = $(me.opts.basketButtonSelector),
                $confirmPage = $(me.opts.confirmPageSelector),
                bbFunction = 'val',
                preSelection = 'none',
                $payPalCheckBox = $(me.opts.paymentMeanSelector + paymentId),
                isConfirmAction = $(me.opts.confirmCheckSelector).length > 0,
                ppp;

            if (!$basketButton.length) {
                $basketButton = $(me.opts.altBasketButtonSelector);
                bbFunction = 'html';
            }

            $basketButton.data('orgValue', $basketButton[bbFunction]());

            me._on($confirmPage, 'submit', $.proxy(me.onConfirm, me));
            me._on($basketButton, 'click', $.proxy(me.onConfirm, me));

            if (!$(me.opts.containerSelector).length) {
                return;
            }

            if ($payPalCheckBox.length > 0 && $payPalCheckBox.prop('checked') || isConfirmAction && me.paypalIsCurrentPaymentMethodPaypal) {
                preSelection = 'paypal';
            }

            ppp = PAYPAL.apps.PPP({
                approvalUrl: me.opts['paypal-unified-approval-url'],
                placeholder: me.opts.placeHolder,
                mode: me.opts['paypal-unified-sandbox'],
                buttonLocation: me.opts.buttonLocation,
                useraction: me.opts.userAction,
                country: me.opts['paypal-unified-country-iso'],
                language: me.opts.language,
                preselection: preSelection,
                showPuiOnSandbox: true,
                showLoadingIndicator: true
            });

            return ppp;
        },

        onConfirm: function(event) {
            var me = this,
                $agb = $(me.opts.agbSelector);

            if (!me.paypalIsCurrentPaymentMethodPaypal || ($agb && $agb.length > 0 && !$agb.prop('checked'))) {
                return;
            }

            event.preventDefault();

            if (window.hasOwnProperty('PAYPAL')) {
                me._ppp.doCheckout();
            }
        },

        deselectPayPalMethod: function() {
            var me = this;

            window.addEventListener('message', $.proxy(me.callback, me), false);
        },

        callback: function(event) {
            var me = this,
                paypalSandbox = me.opts['paypal-unified-sandbox'],
                originUrl = (paypalSandbox === true ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com'),
                data;

            if (event.origin !== originUrl) {
                return;
            }

            data = $.parseJSON(event.data);

            if (data.action !== 'loaded') {
                return;
            }

            if (!me.paypalIsCurrentPaymentMethodPaypal) {
               me._ppp.deselectPaymentMethod();
            }

            window.removeEventListener('message', me.callback, false);
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypal-unified-payment-wall="true"]', 'PayPalUnifiedPaymentWall');
    });
})(jQuery, window);