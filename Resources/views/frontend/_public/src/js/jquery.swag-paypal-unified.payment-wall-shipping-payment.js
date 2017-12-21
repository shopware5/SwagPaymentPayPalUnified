(function($, window, undefined) {
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
             * @type Number
             */
            paypalPaymentId: null,

            /**
             * The selector prefix for the payment method radio input fields
             *
             * @type string
             */
            paymentMethodInputSelectorPrefix: '#payment_mean'
        },

        init: function() {
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
        subscribeEvents: function() {
            var me = this;

            $.subscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/init'), $.proxy(me.onInitPaymentWallPlugin, me));
            $.subscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/enableContinue'), $.proxy(me.onSelectPayPalPaymentMethod, me));
            $.subscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/load'), $.proxy(me.onLoadPaymentWall, me));
            $.subscribe(me.getEventName('plugin/swShippingPayment/onInputChanged'), $.proxy(me.onSelectedPaymentMethodChange, me));
            $.subscribe(me.getEventName('plugin/swShippingPayment/onInputChangedBefore'), $.proxy(me.onBeforeSelectedPaymentMethodChange, me));
            $.subscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/thirdPartyPaymentMethodSelected'), $.proxy(me.onThirdPartyPaymentMethodSelected, me));
        },

        /**
         * Returns the currently selected payment id.
         *
         * @private
         * @method getSelectedPaymentMethodId
         * @returns {Number}
         */
        getSelectedPaymentMethodId: function() {
            var me = this,
                selectedPaymentId;

            selectedPaymentId = $(me.opts.paypalSelectedPaymentMethodRadioSelector).attr('value');

            return parseInt(selectedPaymentId);
        },

        /**
         * Returns the formatted paymentId of a third party payment object
         *
         * @private
         * @method getPaymentIdFromThirdPartyMethod
         * @param {Object} thirdPartyPaymentMethod
         * @return {Number}
         */
        getPaymentIdFromThirdPartyMethod: function(thirdPartyPaymentMethod) {
            return parseInt(thirdPartyPaymentMethod.redirectUrl.substring(7)); // length of "http://"
        },

        /**
         * Selects the given payment radio input field and triggers the change call
         *
         * @private
         * @method triggerPaymentMethodChange
         * @param {Object} $selectedPaymentRadio
         */
        triggerPaymentMethodChange: function($selectedPaymentRadio) {
            $selectedPaymentRadio.prop('checked', true);
            $('*[data-ajax-shipping-payment="true"]').data('plugin_swShippingPayment').onInputChanged();
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
        onInitPaymentWallPlugin: function(event, plugin) {
            var me = this;

            plugin.createPaymentWall(me.opts.paypalPaymentWallSelector);
        },

        /**
         * Will be triggered when the iFrame was completely loaded.
         *
         * @private
         * @method onLoadPaymentWall
         * @param {Object} event
         * @param {Object} plugin
         */
        onLoadPaymentWall: function(event, plugin) {
            var me = this,
                selectedPaymentId = me.getSelectedPaymentMethodId(),
                thirdPartyPaymentMethods = plugin.opts.thirdPartyPaymentMethods,
                clearPaymentSelection = selectedPaymentId !== me.opts.paypalPaymentId,
                thirdPartyPaymentId;

            $.each(thirdPartyPaymentMethods, function(index, thirdPartyPaymentMethod) {
                thirdPartyPaymentId = me.getPaymentIdFromThirdPartyMethod(thirdPartyPaymentMethod);

                if (thirdPartyPaymentId === selectedPaymentId) {
                    clearPaymentSelection = false;
                }
            });

            if (clearPaymentSelection) {
                plugin.clearPaymentSelection();
            }
        },

        /**
         * Will be triggered if any payment method is being selected in the iFrame
         *
         * @private
         * @method onSelectPayPalPaymentMethod
         */
        onSelectPayPalPaymentMethod: function() {
            var me = this,
                $paypalUnifiedRadio = $(me.opts.paymentMethodInputSelectorPrefix + me.opts.paypalPaymentId),
                selectedPaymentId = me.getSelectedPaymentMethodId();

            if (selectedPaymentId !== me.opts.paypalPaymentId && !$paypalUnifiedRadio.prop('checked')) {
                me.triggerPaymentMethodChange($paypalUnifiedRadio);
            }
        },

        /**
         * Will be triggered if the user selects another payment method from the shopware payment method list.
         *
         * @private
         * @method onSelectedPaymentMethodChange
         */
        onSelectedPaymentMethodChange: function() {
            var me = this,
                $pluginContainer = $('*[data-paypalPaymentWall="true"]'),
                paymentWallPlugin = $pluginContainer.data('plugin_swagPayPalUnifiedPaymentWall');

            paymentWallPlugin.createPaymentWall(me.opts.paypalPaymentWallSelector);

            if ($.loadingIndicator.defaults) {
                // We have to restore the default of the loading indicator, since it was
                // updated in the onBeforeSelectedPaymentMethodChange event-handler.
                $.loadingIndicator.defaults.closeOnClick = true;
            }
        },

        /**
         * Will be triggered before the selected payment method changes.
         * It will disable the close on click functionality of the loading indicator
         * overlay to improve the usability.
         *
         * @private
         * @method onBeforeSelectedPaymentMethodChange
         */
        onBeforeSelectedPaymentMethodChange: function() {
            if ($.loadingIndicator.defaults !== undefined) {
                $.loadingIndicator.defaults.closeOnClick = false;
            }
        },

        /**
         * Will be triggered, if a third party payment method is selected in the iFrame
         *
         * @private
         * @method onThirdPartyPaymentMethodSelected
         */
        onThirdPartyPaymentMethodSelected: function(event, plugin, data) {
            var me = this,
                thirdPartyPaymentMethods = plugin.opts.thirdPartyPaymentMethods,
                selectedThirdPartyPaymentMethod = data.thirdPartyPaymentMethod,
                selectedPaymentId = me.getSelectedPaymentMethodId(),
                thirdPartyPaymentId = -1,
                $thirdPartyPaymentRadio;

            $.each(thirdPartyPaymentMethods, function(index, thirdPartyPaymentMethod) {
                if (selectedThirdPartyPaymentMethod !== thirdPartyPaymentMethod.methodName) {
                    return;
                }

                thirdPartyPaymentId = me.getPaymentIdFromThirdPartyMethod(thirdPartyPaymentMethod);
            });

            if (thirdPartyPaymentId === -1 || selectedPaymentId === thirdPartyPaymentId) {
                return;
            }

            $thirdPartyPaymentRadio = $(me.opts.paymentMethodInputSelectorPrefix + thirdPartyPaymentId);
            me.triggerPaymentMethodChange($thirdPartyPaymentRadio);
        },

        /**
         * Destroys the plugin and unsubscribes from subscribed events
         */
        destroy: function() {
            var me = this;

            $.unsubscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/init'));
            $.unsubscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/enableContinue'));
            $.unsubscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/load'));
            $.unsubscribe(me.getEventName('plugin/swShippingPayment/onInputChanged'));
            $.unsubscribe(me.getEventName('plugin/swShippingPayment/onInputChangedBefore'));
            $.unsubscribe(me.getEventName('plugin/swagPayPalUnifiedPaymentWall/thirdPartyPaymentMethodSelected'));

            me._destroy();
        }
    });

    window.StateManager.addPlugin('*[data-paypalPaymentWallShippingPayment="true"]', 'swagPayPalUnifiedPaymentWallShippingPayment');
})(jQuery, window);
