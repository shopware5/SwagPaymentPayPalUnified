;(function($, window) {
    $.plugin('swagPayPalUnifiedSepaEligibility', {
        defaults: {
            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

            /**
             * Holds the client id
             *
             * @type string
             */
            clientId: '',

            /**
             * @type string
             */
            intent: '',

            /**
             * The language ISO (ISO_639) locale of the button.
             *
             * for possible values see: https://developer.paypal.com/api/rest/reference/locale-codes/
             *
             * @type string
             */
            locale: '',

            /**
             * Use PayPal debug mode
             *
             * @type boolean
             */
            useDebugMode: false,

            /**
             * Currency which should be used for the Smart Payment Buttons
             *
             * @type string
             */
            currency: 'EUR',

            /**
             * The Sepa payment method id
             */
            sepaPaymentMethodId: null,

            /**
             * The class name to identify whether or not the paypal sdk has been loaded
             *
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * Selector for the sepa payment method container
             *
             * @type string
             */
            paymentMethodContainerSelector: '.payment--method',

            /**
             * Selector for the sepa payment method input radio button
             *
             * @type string
             */
            paymentMethodInputSelectorTemplate: 'input[value="%s"]'
        },

        init: function() {
            this.applyDataAttributes();

            if (window.location.href.indexOf('sepaIsNotEligible=true') > 0) {
                this.removeSepaPayment();
                return;
            }

            this.createEligibilityCheck();
        },

        createEligibilityCheck: function() {
            var me = this,
                $head = $('head');

            if (!$head.hasClass(this.opts.paypalScriptLoadedSelector)) {
                $.ajax({
                    url: this.renderSdkUrl(),
                    dataType: 'script',
                    cache: true,
                    success: function() {
                        $head.addClass(me.opts.paypalScriptLoadedSelector);

                        me.checkEligibility();
                    }
                });
            } else {
                this.checkEligibility();
            }
        },

        checkEligibility: function() {
            if (!window.paypal.isFundingEligible(window.paypal.FUNDING.SEPA)) {
                this.removeSepaPayment();
            }
        },

        removeSepaPayment: function() {
            var selector = this.opts.paymentMethodInputSelectorTemplate.replace('%s', this.opts.sepaPaymentMethodId);

            $(selector).closest(this.opts.paymentMethodContainerSelector).remove();
        },

        renderSdkUrl: function() {
            var params = {
                'client-id': this.opts.clientId,
                intent: this.opts.intent.toLowerCase(),
                components: 'funding-eligibility'
            };

            if (this.opts.locale.length > 0) {
                params.locale = this.opts.locale;
            }

            if (this.opts.useDebugMode) {
                params.debug = true;
            }

            if (this.opts.currency) {
                params.currency = this.opts.currency;
            }

            return [this.opts.sdkUrl, '?', $.param(params, true)].join('');
        }
    });

    window.StateManager.addPlugin('*[data-swagPayPalUnifiedSepaEligibility="true"]', 'swagPayPalUnifiedSepaEligibility');
})(jQuery, window);
