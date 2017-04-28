/*
    The payment wall plugin for the PayPal Plus integration.
    The following methods can be used to interact with this plugin:

        - createPaymentWall(string parentElement);
            Renders the payment wall into the provided element.
            This element should be a jQuery element of the type div.

        - clearSelection();
            Deselects the payment method.

    In addition to the methods displayed above, the following events can be used:

        - plugin/swagPayPalUnifiedPaymentWall/load
            Will be fired if the payment wall was successfully initialized

        - plugin/swagPayPalUnifiedPaymentWall/enableContinue
            Will be fired if a payment method is being selected in the iframe

        - plugin/swagPayPalUnifiedPaymentWall/disableContinue
            Will be fired if the payment method is being deselected in the iframe

        - plugin/swagPayPalUnifiedPaymentWall/beforeCreate
            Will be fired before the actual payment wall object is being created

        - plugin/swagPayPalUnifiedPaymentWall/afterCreate
            Will be fired after the actual payment wall object was created
 */
;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPaymentWall', {

        /** @object Default plugin configuration */
        defaults: {
            /**
             * The approvalUrl that is returned by the `create payment` call
             *
             * @type string
             */
            paypalApprovalUrl: '',

            /**
             * An ISO-3166 country code.
             * Country in which the PayPal PLUS service will be deployed.
             *
             * @type string
             */
            paypalCountryIso: '',

            /**
             * Depending on the mode, the library will load the PSP from different locations. live will
             * load it from paypal.com whereas sandbox will load it from sandbox.paypal.com. The
             * library will also emit warning to the console if the mode is sandbox (in live mode it will
             * do so only for required parameters).
             *
             * Available modes:
             *  - "live"
             *  - "sandbox"
             *
             * @type string
             */
            paypalMode: 'live',

            /**
             * Determines the location of the Continue button. Don't forget to set the onContinue
             * parameter.
             *
             * Available locations:
             *  - "inside"
             *  - "outside"
             *
             *  @type string
             */
            paypalButtonLocation: 'outside',

            /**
             * Determines if one of the following should be preselected:
             * nothing (="none"),
             * PayPal Wallet (="paypal") or
             * thirdparty method with methodName
             *
             * @type string
             */
            paypalPreSelection: 'paypal',

            /**
             * Checkout flow to be implemented by the Merchant. If not set, the default will be set to
             * the "Continue" flow. The checkout flow selected determines whether the merchant
             * explicitly requires that the buyer reviews and confirms the payment on a review page
             * ("Continue" Flow) or if he/she can confirm payment on PayPal ("Commit" Flow).
             *
             * @type string
             */
            paypalUserAction: 'commit',

            /**
             * The language ISO (ISO_639) for the payment wall.
             *
             * @type string
             */
            paypalLanguage: 'en_US',

            /**
             * If set to "true" it will activate a message that indicates that surcharges will be applied.
             *
             * @type boolean
             */
            paypalSurcharging: false,

            /**
             * If set to "true" it will show a loading spinner until the PSP is completely rendered.
             *
             * @type boolean
             */
            paypalShowLoadingIndicator: true,

            /**
             * If set to "true" PUI is shown in sandbox mode (NOTE: this parameter is ignored in
             * production mode!)
             *
             * @type boolean
             */
            paypalShowPuiOnSandbox: true
        },

        /**
         * @public
         * @type {Boolean}
         */
        loaded: false,

        /**
         * @private
         * @type {Object}
         */
        paymentWall: null,

        /**
         * @private
         * @type {String}
         */
        placeholder: '',

        init: function () {
            var me = this;
            me.applyDataAttributes();

            $.publish('plugin/swagPayPalUnifiedPaymentWall/init', me);
        },

        /**
         * Creates a payment wall iframe inside the provided element
         *
         * @method createPaymentWall
         * @param {String} parent
         */
        createPaymentWall: function (parent) {
            var me = this;

            me.loaded = false;
            me.placeholder = parent;

            $.publish('plugin/swagPayPalUnifiedPaymentWall/beforeCreate', me);

            me.paymentWall = PAYPAL.apps.PPP({
                approvalUrl: me.opts.paypalApprovalUrl,
                placeholder: parent,
                country: me.opts.paypalCountryIso,
                mode: me.opts.paypalMode,
                buttonLocation: me.opts.paypalButtonLocation,
                preselection: me.opts.paypalPreSelection,
                language: me.opts.paypalLanguage,
                useraction: me.opts.paypalUserAction,
                surcharging: me.opts.paypalSurcharging,
                showLoadingIndicator: me.opts.paypalShowLoadingIndicator,
                showPuiOnSandbox: me.opts.paypalShowPuiOnSandbox,
                onLoad: $.proxy(me.onLoad, me),
                enableContinue: $.proxy(me.onEnableContinue, me),
                disableContinue: $.proxy(me.onDisableContinue, me)
            });

            $.publish('plugin/swagPayPalUnifiedPaymentWall/afterCreate', me);
        },

        /**
         * This function deselect any payment method inside the iframe
         *
         * @method createPaymentWall
         */
        clearPaymentSelection: function () {
            var me = this;

            if (me.loaded) {
                me.paymentWall.deselectPaymentMethod();
            }
        },

        /**
         * Will be triggered when the payment wall was initialized.
         *
         * @private
         * @method onLoad
         */
        onLoad: function () {
            var me = this;

            me.loaded = true;
            $.publish('plugin/swagPayPalUnifiedPaymentWall/load', me);
        },

        /**
         * This function will be triggered if the "enableContinue" event was fired inside the iframe.
         * In addition to that, this event can be used to determine if the user has clicked on one of the
         * methods inside the iframe.
         *
         * @private
         * @method onEnableContinue
         */
        onEnableContinue: function () {
            var me = this;

            if (me.loaded) {
                $.publish('plugin/swagPayPalUnifiedPaymentWall/enableContinue', me);
            }
        },

        /**
         * This function will be triggered if the "disableContinue" event was fired inside the iframe.
         * In addition to that, this event can be used to determine if the user has deselected a payment
         * method inside the iframe.
         *
         * @private
         * @method onDisableContinue
         */
        onDisableContinue: function () {
            var me = this;

            if (me.loaded) {
                $.publish('plugin/swagPayPalUnifiedPaymentWall/disableContinue', me);
            }
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypalPaymentWall="true"]', 'swagPayPalUnifiedPaymentWall');
    });
})(jQuery, window);
