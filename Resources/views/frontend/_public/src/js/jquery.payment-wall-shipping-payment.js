(function ($, window, undefined) {

    /**
     * prevent closing of the indicator on click by overwriting the default value
     */
    var initialSetting = $.loadingIndicator.defaults.closeOnClick;
    $.subscribe('plugin/swShippingPayment/onInputChangedBefore', function () {
        $.loadingIndicator.defaults.closeOnClick = false;
    });

    /**
     * event listener which will be triggered if the customer changes their shipping or payment method
     * to call the PayPal payment wall after AJAX request
     */
    $.subscribe('plugin/swShippingPayment/onInputChanged', function (event, plugin) {
        var me = plugin,
            form = me.$el.find(me.opts.formSelector),
            data = form.serializeArray(),
            $paypalPlusContainer = $('#ppplus'),
            paypalPaymentId = window.parseInt($paypalPlusContainer.attr('data-paypal-unified-payment-id'), 10),
            ppPlugin, pppInstance;

        // reset the default
        $.loadingIndicator.defaults.closeOnClick = initialSetting;

        // get instance of the payment wall plugin
        $('*[data-paypal-unified-payment-wall="true"]').PayPalUnifiedPaymentWall();
        ppPlugin = $('*[data-paypal-unified-payment-wall="true"]').data('plugin_PayPalUnifiedPaymentWall');

        pppInstance = ppPlugin.createPaymentWall(paypalPaymentId);

        var paymentId = -1;
        $.each(data, function (i, item) {
            if (item.hasOwnProperty('name') && item.name === 'payment') {
                paymentId = window.parseInt(item.value, 10);
                return false;
            }
        });

        $paypalPlusContainer.find('iframe').one('load', function () {
            if (paymentId !== -1 && paymentId !== paypalPaymentId) {
                pppInstance.deselectPaymentMethod();
            }
        });
    });
})(jQuery, window);

(function($, window) {
    'use strict';

    $.plugin('PayPalUnifiedPaymentListener', {
        /** @object Default plugin configuration */
        defaults: {
            /** @string default selector for confirm check */
            confirmCheckSelector: '.is--act-confirm',
            /** @string default selector for the PayPal Plus container */
            containerSelector: '#ppplus',
            /** @string default sandbox usage */
            'paypal-unified-sandbox': '',
            /** @string default selector for the payment mean */
            paymentMeanSelector: '#payment_mean'
        },

        /**
         * Initializes the plugin
         *
         * @returns void
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();
            me.initEventListener();
            me.events = [];
        },

        isClick: function () {
            var me = this;

            return me.events.indexOf('loaded') == -1;
        },

        initEventListener: function () {
            var me = this,
                timeOut;

            window.addEventListener('message', function (event) {
                var paypalSandbox = me.opts['paypal-unified-sandbox'],
                    originUrl = (paypalSandbox === 1 ? 'https://www.sandbox.paypal.com' : 'https://www.paypal.com'),
                    isConfirmAction = $(me.opts.confirmCheckSelector).length > 0;

                if (isConfirmAction) {
                    return false;
                }

                if (event.origin !== originUrl) {
                    return false;
                }

                if (timeOut !== undefined) {
                    clearTimeout(timeOut);
                }

                var data = $.parseJSON(event.data);

                me.events.push(data.action);
                //wait until all events are fired
                timeOut = setTimeout(function () {
                    me.handleEvents();
                }, 500);
            }, false);
        },

        handleEvents: function () {
            var me = this,
                $paypalPlusContainer = $(me.opts.containerSelector),
                paypalPaymentId = $paypalPlusContainer.attr('data-paypal-unified-payment-id'),
                payPalCheckBox = $(me.opts.paymentMeanSelector + paypalPaymentId);

            if (me.isClick()) {
                if (!payPalCheckBox.prop('checked')) {
                    payPalCheckBox.prop('checked', true);
                    $('*[data-ajax-shipping-payment="true"]').data('plugin_swShippingPayment').onInputChanged();
                }
            }
            me.events = [];
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypal-unified-payment-wall="true"]', 'PayPalUnifiedPaymentListener');
    });
})(jQuery, window);