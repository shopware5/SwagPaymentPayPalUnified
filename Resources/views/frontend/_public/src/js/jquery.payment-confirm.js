/* global PAYPAL */

;(function($, window) {
    'use strict';

    $.plugin('PayPalUnifiedConfirmPayment', {

        /** @object Default plugin configuration */
        defaults: {
            /** @string default selector for confirm page */
            confirmPageSelector: '#confirm--form',
            /** @string default selector agb checkbox */
            agbSelector: '#sAGB',
            /** @string default address patch url */
            paypalUnifiedAddressPatchUrl: '',
            /** @string default remote paypal payment id */
            paypalUnifiedRemotePaymentId: '',
            /** @string default paypal error page used for redirection */
            paypalUnifiedErrorPage: ''
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
                me.patchPaymentAddress();
            }
        },

        patchPaymentAddress: function () {
            var me = this,
                remotePaymentId = me.opts.paypalUnifiedRemotePaymentId;

            $.ajax({
                url: me.opts.paypalUnifiedAddressPatchUrl,
                data: { paymentId: remotePaymentId },
                method: 'GET',
                success: $.proxy(me.addressPatchAjaxCallbackSuccess, me),
                error: $.proxy(me.addressPatchAjaxCallbackError, me)
            });
        },

        addressPatchAjaxCallbackSuccess: function () {
            PAYPAL.apps.PPP.doCheckout();
        },

        addressPatchAjaxCallbackError: function () {
            var me = this;

            $(location).attr('href', me.opts.paypalUnifiedErrorPage);
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypalUnifiedConfirmPayment="true"]', 'PayPalUnifiedConfirmPayment');
    });
})(jQuery, window);
