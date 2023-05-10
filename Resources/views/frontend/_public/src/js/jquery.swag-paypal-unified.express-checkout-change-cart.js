;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedExpressCheckoutChangeCart', {
        defaults: {
            addVoucherFormSelector: '.add-voucher--form',

            expressCheckoutParameterKey: 'expressCheckout',

            expressCheckoutTokenKey: 'token',

            payPalCartHasChangedKey: 'payPalCartHasChanged'
        },

        init: function() {
            this.applyDataAttributes();

            if (!this.checkHasToken()) {
                return;
            }

            this.$form = this.getForm();
            this.updateFormAction();
        },

        /**
         * @returns { boolean }
         */
        checkHasToken: function() {
            this.loadParams();

            if (!this.payPalToken) {
                return false;
            }

            return true;
        },

        loadParams: function() {
            var urlParams = new URLSearchParams(window.location.search);

            this.payPalToken = urlParams.get(this.opts.expressCheckoutTokenKey);
        },

        getForm: function() {
            return this.$el.find('form');
        },

        updateFormAction: function() {
            var actionUrl = new URL(this.$form[0].action);
            actionUrl.searchParams.set(this.opts.expressCheckoutParameterKey, true);
            actionUrl.searchParams.set(this.opts.expressCheckoutTokenKey, this.payPalToken);
            actionUrl.searchParams.set(this.opts.payPalCartHasChangedKey, true);

            this.$form.attr('action', actionUrl.toString());
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedEcButtonChangeCart="true"]', 'swagPayPalUnifiedExpressCheckoutChangeCart');
})(jQuery, window);
