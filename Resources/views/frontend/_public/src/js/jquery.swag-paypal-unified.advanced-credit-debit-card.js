;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedAdvancedCreditDebitCard', {
        defaults: Object.assign($.swagPayPalCreateDefaultPluginConfig(), {
            /**
             * @type string
             */
            paypalScriptId: 'swagPayPalUnifiedPayPalSdk',

            /**
             * @type string
             */
            formSelector: '#paypal-acdc-form',

            /**
             * @type string
             */
            acdcNumberSelector: '#paypal-acdc-number',

            /**
             * @type string
             */
            acdcExpirationSelector: '#paypal-acdc-expiration',

            /**
             * @type string
             */
            acdcCvvSelector: '#paypal-acdc-cvv',

            /**
             * @type string
             */
            errorContainerSelector: '.paypal-unified--error',

            /**
             * @type string
             */
            autoResizerSelector: '*[data-panel-auto-resizer="true"]',

            /**
             * @type string
             */
            onSubmitErrorMessageSelector: '.paypal--acdc-submit-error',

            /**
             * @type string
             */
            createOrderUrl: '',

            /**
             * @type string
             */
            captureUrl: '',

            /**
             * @type string
             */
            errorUrl: '',

            /**
             * @type string
             */
            clientToken: '',

            /**
             * Cardholder Data for the hosted fields
             *
             * @type string
             */
            cardHolderData: '',

            /**
             * @type boolean
             */
            hostedFieldsError: false,

            /**
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             * @type string
             */
            hasErrorClass: 'has--error',

            /**
             * @type string
             */
            preloaderPluginName: 'plugin_swPreloaderButton',

            /**
             * @type string
             */
            resizerPluginName: 'plugin_swPanelAutoResizer',

            /**
             * @type string
             */
            placeholderCardNumber: '',

            /**
             * @type string
             */
            placeholderExpiryDate: '',

            /**
             * @type string
             */
            placeholderSecurityCode: ''
        }),

        init: function() {
            this.applyDataAttributes();

            this.$submitBtn = $(this.opts.confirmFormSubmitButtonSelector);
            this.$submitBtn.prop('disabled', true);

            this.insertScript();
            this.registerEventListeners();

            $.publish('plugin/swagPayPalUnifiedAdvancedCreditDebitCard/init', this);
        },

        insertScript: function() {
            var $head = $('head');

            if (!$head.hasClass(this.opts.paypalScriptLoadedSelector)) {
                var payPalScript = document.createElement('script');

                payPalScript.id = this.opts.paypalScriptId;
                payPalScript.src = this.renderSdkUrl();
                payPalScript.dataset.clientToken = this.opts.clientToken;
                payPalScript.async = true;
                payPalScript.addEventListener('load', this.renderHostedFields.bind(this), false);

                document.head.appendChild(payPalScript);
                $head.addClass(this.opts.paypalScriptLoadedSelector);
            } else {
                this.renderHostedFields();
            }
        },

        registerEventListeners: function() {
            $.subscribe('plugin/swPreloaderButton/onShowPreloader', this.resetPreloaderPlugin.bind(this, false));
            $.subscribe('plugin/swagPayPalUnifiedAdvancedCreditDebitCard/captureOrderFinished', this.resetPreloaderPlugin.bind(this, true));
        },

        /**
         * @return { Object }
         */
        getFieldsConfig: function() {
            return {
                number: {
                    selector: this.opts.acdcNumberSelector,
                    placeholder: this.opts.placeholderCardNumber
                },
                expirationDate: {
                    selector: this.opts.acdcExpirationSelector,
                    placeholder: this.opts.placeholderExpiryDate
                },
                cvv: {
                    selector: this.opts.acdcCvvSelector,
                    placeholder: this.opts.placeholderSecurityCode
                }
            };
        },

        /**
         * @return { string }
         */
        renderSdkUrl: function() {
            var params = {
                'client-id': this.opts.clientId,
                components: 'hosted-fields,buttons',
                currency: this.opts.currency
            };

            if (this.opts.locale.length > 0) {
                params.locale = this.opts.locale;
            }

            if (this.opts.useDebugMode) {
                params.debug = true;
            }

            return $.swagPayPalRenderUrl(this.opts.sdkUrl, params);
        },

        renderHostedFields: function() {
            if (!window.paypal) {
                throw new Error('SDK not initialised yet.');
            }

            // Toggles activity of the fallback plugin.
            if (!window.paypal.HostedFields.isEligible()) {
                window.StateManager.addPlugin('*[data-swagPayPalUnifiedAdvancedCreditDebitCard="true"]', 'swagPayPalUnifiedAdvancedCreditDebitCardFallback');
                return;
            }

            var me = this;

            paypal.HostedFields.render({
                createOrder: me.createPaypalOrder.bind(me),
                fields: me.getFieldsConfig()
            }).then(me.bindFieldActions.bind(me))
                .then(me.showHostedFields.bind(me));
        },

        showHostedFields: function() {
            this.$el.removeClass(this.opts.hiddenClass);
            this.updateAutoResizer();
            this.$submitBtn.prop('disabled', false);
        },

        /**
         * @return {*}
         */
        createPaypalOrder: function() {
            var me = this;

            return $.ajax({
                type: 'POST',
                url: this.opts.createOrderUrl
            }).then(
                me.onCreatePaypalOrderSuccess.bind(me),
                me.onError.bind(me)
            );
        },

        bindFieldActions: function(hostedFields) {
            var $orderForm = $(this.opts.confirmFormSelector);

            $orderForm.on('submit.paypalUnified', this.onSubmitForm.bind(this, hostedFields));

            hostedFields.on('validityChange', this.onValidityChange.bind(this));
        },

        /**
         * @param event { Event }
         */
        onValidityChange: function (event) {
            var field = event.fields[event.emittedBy];

            if (field.isValid || field.isPotentiallyValid) {
                field.container.classList.remove(this.opts.hasErrorClass);
            }
        },

        /**
         * @param hostedFields { Object }
         * @param event { Event }
         */
        onSubmitForm: function(hostedFields, event) {
            event.preventDefault();

            this.opts.hostedFieldsError = this.validateForm(hostedFields);

            if (!Object.prototype.hasOwnProperty.call(this.opts.cardHolderData, 'contingencies')) {
                this.opts.cardHolderData.cardholderName = $('#card-holder-name').val();
                this.opts.cardHolderData.billingAddress.postalCode = $('#card-billing-address-zip').val();
            }

            if (!this.opts.hostedFieldsError) {
                $.loadingIndicator.open({
                    openOverlay: true,
                    closeOnClick: false,
                    theme: 'light'
                });

                try {
                    $.loadingIndicator.loader.$loader.css('z-index', 990);
                    $.loadingIndicator.loader.overlay.$overlay.css('z-index', 990);
                } catch (e) {
                    // Compatibility with older versions
                    $.loadingIndicator.$loader.css('z-index', 990);
                    $.overlay.getElement().css('z-index', 990);
                }

                hostedFields.submit(this.opts.cardHolderData)
                    .then(this.captureOrder.bind(this))
                    .catch(this.onSubmitError.bind(this));
            }
        },

        onSubmitError: function() {
            $(this.opts.onSubmitErrorMessageSelector).removeClass(this.opts.hiddenClass);

            this.resetPreloaderPlugin(true);

            $.loadingIndicator.close();
        },

        /**
         * @param hostedFields { Object }
         *
         * @return { boolean }
         */
        validateForm: function(hostedFields) {
            var me = this,
                hasError = false;

            this.$el.find(this.opts.errorContainerSelector).remove();

            $.each(hostedFields.getState().fields, function(key, value) {
                if (!value.isValid) {
                    if (!hasError) {
                        value.container.scrollIntoView();
                    }

                    hasError = true;
                    value.container.classList.add(me.opts.hasErrorClass);
                } else {
                    value.container.classList.remove(me.opts.hasErrorClass);
                }
            });

            return hasError;
        },

        /**
         * @param response { Response }
         *
         * @return { String }
         */
        onCreatePaypalOrderSuccess: function(response) {
            return response.token;
        },

        /**
         * @param response { Response }
         */
        captureOrder: function(response) {
            $.ajax({
                type: 'POST',
                url: this.opts.captureUrl,
                data: {
                    token: response.orderId
                },
                success: this.submitForm.bind(this, response.orderId),
                error: this.onError.bind(this),
                complete: this.onComplete.bind(this)
            });
        },

        /**
         * @param paypalOrderId { String }
         */
        submitForm: function(paypalOrderId) {
            var $orderForm = $(this.opts.confirmFormSelector),
                input = document.createElement('input');

            input.setAttribute('type', 'hidden');
            input.setAttribute('name', 'token');
            input.setAttribute('value', paypalOrderId);

            $orderForm.append(input);

            $orderForm.off('submit.paypalUnified');

            $orderForm.submit();
        },

        /**
         * @param response { Response }
         */
        onError: function(response) {
            var jsonResponse,
                content;

            if (response.responseText !== '') {
                jsonResponse = JSON.parse(response.responseText);

                if (jsonResponse.redirectTo) {
                    window.location.replace(jsonResponse.redirectTo);

                    return;
                }

                content = jsonResponse.errorTemplate;
                this.$el.prepend(content);
            }

            this.updateAutoResizer();

            this.resetPreloaderPlugin(true);

            $.loadingIndicator.close();
        },

        onComplete: function() {
            $.publish('plugin/swagPayPalUnifiedAdvancedCreditDebitCard/captureOrderFinished', this);
        },

        /**
         * @param {boolean} force
         */
        resetPreloaderPlugin: function(force) {
            if (!this.opts.hostedFieldsError && !force) {
                return;
            }

            $(this.opts.confirmFormSubmitButtonSelector).data(this.opts.preloaderPluginName).reset();
        },

        updateAutoResizer: function() {
            this.$el.parents(this.opts.autoResizerSelector).data(this.opts.resizerPluginName).update();
        }
    });

    window.StateManager.addPlugin('*[data-swagPayPalUnifiedAdvancedCreditDebitCard="true"]', 'swagPayPalUnifiedAdvancedCreditDebitCard');
})(jQuery, window);
