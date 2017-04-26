(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPaymentWallShippingPayment', {
        defaults: {
            /**
             * The selector of the element in which the payment wall should
             * be created.
             *
             * @type string
             */
            paypalPaymentWallSelector: 'ppplus',

            /**
             * The jQuery selector that gets the currently checked payment method radio button.
             *
             * @type string
             */
            paypalSelectedPaymentMethodRadioSelector: '*[checked="checked"][name="payment"]',

            /**
             * The payment id of PayPal Unified.
             *
             * @type Numeric
             */
            paypalPaymentId: null
        },

        init: function () {
            var me = this;

            me.applyDataAttributes();
            me.subscribeEvents();

            $.publish('plugin/swagPayPalUnifiedPaymentWallShippingPayment/init', me);
        },

        /**
         * Subscribes the events that are required to run this instance.
         *
         * @private
         * @method subscribeEvents
         */
        subscribeEvents: function () {
            var me = this;

            $.subscribe('plugin/swagPayPalUnifiedPaymentWall/init', $.proxy(me.onInitPaymentWallPlugin, me));
            $.subscribe('plugin/swagPayPalUnifiedPaymentWall/enableContinue', $.proxy(me.onSelectPayPalPaymentMethod, me));
            $.subscribe('plugin/swagPayPalUnifiedPaymentWall/load', $.proxy(me.onLoadPaymentWall, me));
            $.subscribe('plugin/swShippingPayment/onInputChanged', $.proxy(me.onSelectedPaymentMethodChange, me));
            $.subscribe('plugin/swShippingPayment/onInputChangedBefore', $.proxy(me.onBeforeSelectedPaymentMethodChange, me));
        },

        /**
         * Returns the currently selected payment id.
         *
         * @private
         * @method getSelectedPaymentMethodId
         * @returns {Numeric}
         */
        getSelectedPaymentMethodId: function () {
            var me = this;

            return $(me.opts.paypalSelectedPaymentMethodRadioSelector).attr('value');
        },

        /**
         * Will be triggered when the swPayPalUnifiedPaymentWall-Plugin was initialized.
         * Creates the payment wall.
         *
         * @private
         * @method onInitPaymentWallPlugin
         * @param {Object} event
         * @param {Object} plugin
         */
        onInitPaymentWallPlugin: function (event, plugin) {
            var me = this;

            plugin.createPaymentWall(me.opts.paypalPaymentWallSelector);
        },

        /**
         * Will be triggered when the iframe was completely loaded.
         *
         * @private
         * @method onLoadPaymentWall
         * @param {Object} event
         * @param {Object} plugin
         */
        onLoadPaymentWall: function(event, plugin) {
            var me = this,
                selectedPaymentId = me.getSelectedPaymentMethodId();

            if (parseInt(selectedPaymentId) !== parseInt(me.opts.paypalPaymentId)) {
                plugin.clearPaymentSelection();
            }
        },

        /**
         * Will be triggered if any payment method is being selected in the iframe
         *
         * @private
         * @method onSelectPayPalPaymentMethod
         */
        onSelectPayPalPaymentMethod: function () {
            var me = this,
                $paypalUnifiedRadio = $('#payment_mean' + me.opts.paypalPaymentId),
                selectedPaymentId = me.getSelectedPaymentMethodId();

            if (parseInt(selectedPaymentId) !== parseInt(me.opts.paypalPaymentId) && !$paypalUnifiedRadio.prop('checked')) {
                    $paypalUnifiedRadio.prop('checked', true);
                    $('*[data-ajax-shipping-payment="true"]').data('plugin_swShippingPayment').onInputChanged();
            }
        },

        /**
         * Will be triggered if the user selects another payment method from the shopware payment method list.
         *
         * @private
         * @method onSelectedPaymentMethodChange
         */
        onSelectedPaymentMethodChange: function () {
            var me = this,
                $pluginContainer = $('*[data-paypalPaymentWall="true"]'),
                paymentWallPlugin = $pluginContainer.data('plugin_swagPayPalUnifiedPaymentWall');

            paymentWallPlugin.createPaymentWall(me.opts.paypalPaymentWallSelector);

            // We have to restore the default of the loading indicator, since it was
            // updated in the onBeforeSelectedPaymentMethodChange event-handler.
            $.loadingIndicator.defaults.closeOnClick = true;
        },

        /**
         * Will be triggered before the selected payment method changes.
         * It will disable the close on click functionality of the loading indicator
         * overlay to improve the usability.
         *
         * @private
         * @method onBeforeSelectedPaymentMethodChange
         */
        onBeforeSelectedPaymentMethodChange: function () {
            $.loadingIndicator.defaults.closeOnClick = false;
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypalPaymentWallShippingPayment="true"]', 'swagPayPalUnifiedPaymentWallShippingPayment');
    });
})(jQuery, window);
