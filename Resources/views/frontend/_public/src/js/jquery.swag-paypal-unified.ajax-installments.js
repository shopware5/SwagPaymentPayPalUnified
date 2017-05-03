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
            paypalInstallmentsContainerSelector: '.paypal--installments'
        },

        /**
         *
         */
        init: function () {
            var me = this;
            me.applyDataAttributes();

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/init', me);

            me.requestDetails();
        },

        /**
         * Requests the financing details from the installments controller.
         *
         * @private
         * @method requestDetails
         */
        requestDetails: function () {
            var me = this;

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/requestDetails', me);

            $.ajax({
                url: me.opts.paypalInstallmentsRequestUrl,
                data: {
                    productPrice: me.opts.paypalInstallmentsProductPrice
                },
                method: 'GET',
                success: $.proxy(me.detailsAjaxCallbackSuccess, me),
                error: $.proxy(me.detailsAjaxCallbackError, me)
            });
        },

        detailsAjaxCallbackSuccess: function (response) {
            var me = this,
                $loadingIndicator = $(me.opts.paypalLoadingIndicatorSelector),
                $installmentsContainer = $(me.opts.paypalInstallmentsContainerSelector);

            $installmentsContainer.html(response);

            $loadingIndicator.prop('hidden', true);

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/ajaxSuccess')
        },

        /**
         * Will be triggered when the ajax callback fails.
         *
         * @private
         * @method detailsAjaxCallbackError
         */
        detailsAjaxCallbackError: function () {
            var me = this,
                $loadingIndicator = $(me.opts.paypalLoadingIndicatorSelector);

            $loadingIndicator.prop('hidden', true);

            $.publish('plugin/swagPayPalUnifiedAjaxInstallments/ajaxError')
        }
    });

    $(function() {
        StateManager.addPlugin('*[data-paypalAjaxInstallments="true"]', 'swagPayPalUnifiedAjaxInstallments');
    });
})(jQuery, window);
