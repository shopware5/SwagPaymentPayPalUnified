;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedExpressAddressPatch', {
        defaults: {
            patchAddressUrl: null,

            requireAddressPatchKey: 'rap',

            tokenKey: 'token'
        },

        init: function() {
            this.applyDataAttributes();

            this.registerEvents();

            if (this.requireAddressUpdate()) {
                this.callUpdateAddress();
            }
        },

        /**
         * @returns { boolean }
         */
        requireAddressUpdate: function() {
            return this.getUrlSearchParameter().get(this.opts.requireAddressPatchKey) === 'true';
        },

        callUpdateAddress: function() {
            var me = this;

            $.loadingIndicator.open({
                openOverlay: true,
                closeOnClick: false,
                theme: 'light'
            });

            $.ajax({
                url: this.opts.patchAddressUrl,
                dataType: 'json',
                cache: true,
                data: {
                    token: this.getUrlSearchParameter().get(this.opts.tokenKey)
                },
                success: function() {
                    me.updateUrl(false);
                }
            });
        },

        registerEvents: function() {
            $.subscribe('plugin/swAddressSelection/onAfterSave', this.onAfterSaveAddress.bind(this));
            $.subscribe('plugin/swAddressEditor/onAfterSave', this.onAfterSaveAddress.bind(this));
        },

        onAfterSaveAddress: function() {
            this.updateUrl(true);
        },

        /**
         * @param { boolean } requireAddressPatch
         */
        updateUrl: function(requireAddressPatch) {
            var url = new URL(window.location);
            url.searchParams.set(this.opts.requireAddressPatchKey, requireAddressPatch.toString());

            window.location.href = url.toString();
            window.history.pushState({ path: url.toString() }, '', url.toString());
        },

        /**
         * @returns { URLSearchParams }
         */
        getUrlSearchParameter: function() {
            return new URLSearchParams(window.location.search);
        }
    });

    window.StateManager.addPlugin('*[data-swagPayPalUnifiedExpressAddressPatch="true"]', 'swagPayPalUnifiedExpressAddressPatch');
})(jQuery, window);
