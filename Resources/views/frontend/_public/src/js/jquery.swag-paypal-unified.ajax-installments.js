/**
 *  Loads the details using an ajax call.
 *
 *  Methods:
 *      requestDetails()
 *          - Requests the details using the configuration object.
 *
 *  Events:
 *      plugin/swagPayPalUnifiedAjaxInstallments/init
 *          - Will be fired when this plugin was initialized.
 *
 *      plugin/swagPayPalUnifiedAjaxInstallments/beforeRequest
 *          - Will be fired before the actual ajax request was triggered
 *
 *      plugin/swagPayPalUnifiedAjaxInstallments/afterRequest
 *          - Will be fired after the actual ajax request was triggered.
 *              NOTE: Don't expect any ajax result yet.
 *
 *      plugin/swagPayPalUnifiedAjaxInstallments/requestCheapestRate
 *          - Will be fired when the cheapest rate was requested
 *              NOTE: Don't expect any ajax result yet.
 *
 *      plugin/swagPayPalUnifiedAjaxInstallments/requestCompleteList
 *          - Will be fired when all rates were requested
 *              NOTE: Don't expect any ajax result yet.
 *
 *      plugin/swagPayPalUnifiedAjaxInstallments/ajaxSuccess
 *          - Will be fired when the ajax request was successfully
 *
 *      plugin/swagPayPalUnifiedAjaxInstallments/ajaxError
 *          - Will be fired when the ajax request failed
 */
;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedAjaxInstallments', {
        defaults: {
            /**
             * The URL for the ajax request that requests the financing data.
             *
             * @type string
             */
            paypalInstallmentsRequestUrl: '',

            /**
             * The price of the product on which base the details are being requested.
             *
             * @type float
             */
            paypalInstallmentsProductPrice: null,

            /**
             * The selector for the paypal loading indicator.
             *
             * @type string
             */
            paypalLoadingIndicatorSelector: '.paypal-unified-installments--loading-indicator',

            /**
             * The selector for the paypal installments container.
             * The result of the ajax request will be displayed in this element.
             *
             * @type string
             */
            paypalInstallmentsContainerSelector: '.paypal--installments',

            /**
             * The type of the target page.
             * This value is required for the template to load correctly.
             *
             * @type string
             */
            paypalInstallmentsPageType: '',

            /**
             * A value indicating if a complete list of all options or just the cheapest one
             * should be received.
             *
             * @type boolean
             */
            paypalInstallmentsRequestCompleteList: false,

            /**
             * The URL for the complete list ajax request.
             *
             * @type string
             */
            paypalInstallmentsRequestCompleteListUrl: ''
        },

        /**
         * @public
         * @method init
         */
        init: function() {
            var me = this;
            me.applyDataAttributes();

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/init', me);

            me.requestDetails();
        },

        /**
         * Requests the financing details from the installments controller.
         *
         * @public
         * @method requestDetails
         */
        requestDetails: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/beforeRequest', me);

            if (me.opts.paypalInstallmentsRequestCompleteList) {
                me.requestCompleteList();
            } else {
                me.requestCheapestRate();
            }

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/afterRequest', me);
        },

        /**
         * Requests only the cheapest rate from the API.
         *
         * @private
         * @method requestCheapestRate
         */
        requestCheapestRate: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/requestCheapestRate', me);

            $.ajax({
                url: me.opts.paypalInstallmentsRequestUrl,
                data: {
                    productPrice: me.opts.paypalInstallmentsProductPrice,
                    pageType: me.opts.paypalInstallmentsPageType
                },
                method: 'GET',
                success: $.proxy(me.detailsAjaxCallbackSuccess, me),
                error: $.proxy(me.detailsAjaxCallbackError, me)
            });
        },

        /**
         * Requests all rates for the provided price from the API.
         *
         * @private
         * @requestCompleteList
         */
        requestCompleteList: function() {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/requestCompleteList', me);

            $.ajax({
                url: me.opts.paypalInstallmentsRequestCompleteListUrl,
                data: {
                    productPrice: me.opts.paypalInstallmentsProductPrice
                },
                method: 'GET',
                success: $.proxy(me.detailsAjaxCallbackSuccess, me),
                error: $.proxy(me.detailsAjaxCallbackError, me)
            });
        },

        /**
         * Will be triggered when the ajax callback succeeds.
         *
         * @private
         * @method detailsAjaxCallbackSuccess
         * @param { Object } response
         */
        detailsAjaxCallbackSuccess: function(response) {
            var me = this,
                $loadingIndicator = $(me.opts.paypalLoadingIndicatorSelector),
                $installmentsContainer = $(me.opts.paypalInstallmentsContainerSelector);

            $installmentsContainer.html(response);

            $loadingIndicator.prop('hidden', true);

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/ajaxSuccess', me);
        },

        /**
         * Will be triggered when the ajax callback fails.
         *
         * @private
         * @method detailsAjaxCallbackError
         */
        detailsAjaxCallbackError: function() {
            var me = this,
                $loadingIndicator = $(me.opts.paypalLoadingIndicatorSelector);

            $loadingIndicator.prop('hidden', true);

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/ajaxError', me);
        }
    });

    /**
     *  After the loading another product-variant, we lose the
     *  plugin instance, therefore, we have to re-initialize it here.
     */
    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        window.StateManager.addPlugin('*[data-paypalAjaxInstallments="true"]', 'swagPayPalUnifiedAjaxInstallments');

        window.StateManager.addPlugin('*[data-paypalAjaxInstallments="true"]', 'swagPayPalUnifiedAjaxInstallments');
    });

    window.StateManager.addPlugin('*[data-paypalAjaxInstallments="true"]', 'swagPayPalUnifiedAjaxInstallments');
})(jQuery, window);
