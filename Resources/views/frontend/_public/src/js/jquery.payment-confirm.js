;(function($, window) {
    'use strict';

    $.plugin('PayPalUnifiedConfirmPayment', {

        /** @object Default plugin configuration */
        defaults: {
            /** @string default selector for confirm page */
            confirmPageSelector: '#confirm--form',
            /** @string default selector agb checkbox */
            agbSelector: '#sAGB'
        },

        /**
         * Initializes the plugin
         *
         * @returns void
         */
        init: function () {
            var me = this;

            me.applyDataAttributes();
            me.subscribeEvents();
            $.publish('plugin/PayPalUnifiedConfirmPayment/init', [me, me.$parent]);
        },

        subscribeEvents: function () {
            var me = this,
                $confirmPage = $(me.opts.confirmPageSelector);

            me._on($confirmPage, 'submit', $.proxy(me.onConfirm, me));
        },

        onConfirm: function(event) {
            var me = this,
                $agb = $(me.opts.agbSelector);

            if ($agb && $agb.length > 0 && !$agb.prop('checked')) {
                return;
            }

            event.preventDefault();

            if (window.hasOwnProperty('PAYPAL')) {
                PAYPAL.apps.PPP.doCheckout();
            }
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypalUnifiedConfirmPayment="true"]', 'PayPalUnifiedConfirmPayment');
    });
})(jQuery, window);