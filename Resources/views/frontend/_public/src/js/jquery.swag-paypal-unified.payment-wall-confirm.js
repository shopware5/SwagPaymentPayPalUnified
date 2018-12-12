;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPaymentWallConfirm', {
        defaults: {
            /**
             * A value indicating whether or not
             * the user came from the payment selection page.
             * Depending on the value, the payment wall will be created or not
             */
            paypalCameFromPaymentSelection: false,

            /**
             * The selector of the element in which the payment wall should
             * be created.
             *
             * @type string
             */
            paypalPaymentWallSelector: 'ppplus',

            /**
             * The selector of the confirm formula.
             *
             * @type string
             */
            paypalConfirmPageSelector: '#confirm--form',

            /**
             * The URL for the payment address patch.
             *
             * @type string
             */
            paypalAddressPatchUrl: '',

            /**
             * The remote payment id. Will be used to patch the address
             *
             * @type string
             */
            paypalRemotePaymentId: '',

            /**
             * This page will be opened when the confirm process fails.
             *
             * @type string
             */
            paypalErrorPage: ''
        },

        init: function() {
            var me = this;
            me.applyDataAttributes();
            me.subscribeEvents();

            $.publish('plugin/swagPayPalUnifiedPaymentWallConfirm/init', me);
        },

        /**
         * Subscribes the events that are required to run this instance.
         *
         * @private
         * @method subscribeEvents
         */
        subscribeEvents: function() {
            var me = this,
                $confirmPage = $(me.opts.paypalConfirmPageSelector);

            $.subscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/init'), $.proxy(me.onInitPaymentWallPlugin, me));

            me._on($confirmPage, 'submit', $.proxy(me.onConfirmCheckout, me));
        },

        /**
         * Patches the customer's address into the payment object.
         */
        patchPaymentAddress: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedPaymentWall/beforePatchAddress', me);

            var $customerCommentField = $(".user-comment--hidden");

            $.ajax({
                url: me.opts.paypalAddressPatchUrl,
                data: {
                    paymentId: me.opts.paypalRemotePaymentId,
                    sComment: $customerCommentField.val()
                },
                method: 'POST',
                success: $.proxy(me.addressPatchAjaxCallbackSuccess, me),
                error: $.proxy(me.addressPatchAjaxCallbackError, me)
            });
        },

        /**
         * Will be triggered when the payment wall plugin was initialized.
         * If the user didn't come from the payment selection, the payment wall will be displayed.
         *
         * @param {Object} event
         * @param {Object} plugin
         */
        onInitPaymentWallPlugin: function(event, plugin) {
            var me = this;

            if (!me.opts.paypalCameFromPaymentSelection) {
                plugin.createPaymentWall(me.opts.paypalPaymentWallSelector);
            }
        },

        /**
         * Will be triggered when the confirm formula was submitted.
         * In this case, the address data will be patched and the paypal
         * checkout process will be triggered
         *
         * @private
         * @method onConfirmCheckout
         * @param {Object} event
         */
        onConfirmCheckout: function(event) {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedPaymentWallConfirm/confirmCheckout', me);

            event.preventDefault();

            me.patchPaymentAddress();
        },

        /**
         * Will be triggered when the ajax patch request was successful
         *
         * @private
         * @method addressPatchAjaxCallbackSuccess
         */
        addressPatchAjaxCallbackSuccess: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedPaymentWall/afterPatchAddress', me);

            // Let PayPal process its checkout
            PAYPAL.apps.PPP.doCheckout();
        },

        /**
         * Will be triggered when the ajax patch failed.
         *
         * @private
         * @method addressPatchAjaxCallbackSuccess
         */
        addressPatchAjaxCallbackError: function () {
            var me = this,
                redirectUrl = me.opts.paypalErrorPage;

            $.publish('plugin/swagPayPalUnifiedPaymentWall/afterPatchAddress', me);

            /**
             * We need to call 2 different error pages. One for a validation error
             * and the other for general errors. The default error code 2 comes from the template.
             */
            if (arguments[2] === 'Unprocessable Entity') {
                redirectUrl = me.stripErrorCodeFromUrl(redirectUrl) + '7';
                $(location).attr('href', redirectUrl);
            }

            $(location).attr('href', redirectUrl);
        },

        /**
         * Destroys the plugin and unsubscribes from subscribed events
         */
        destroy: function() {
            var me = this;

            $.unsubscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/init'));

            me._destroy();
        },

        stripErrorCodeFromUrl: function (url) {
            var index = url.lastIndexOf('/');

            return url.slice(0, index + 1);
        }
    });

    window.StateManager.addPlugin('*[data-paypalPaymentWallConfirm="true"]', 'swagPayPalUnifiedPaymentWallConfirm');
})(jQuery, window);
