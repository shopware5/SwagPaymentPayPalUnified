;(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPolling', {
        defaults: {
            /**
             * @type string
             */
            pollingUrl: '',

            /**
             * @type string
             */
            successUrl: '',

            /**
             * @type string
             */
            errorUrl: '',

            /**
             * @type int
             */
            pollingInterval: 2000
        },

        init: function () {
            this.applyDataAttributes();

            this.poll();
        },

        poll() {
            $.ajax({
                url: this.opts.pollingUrl,
                type: 'get',
                statusCode: {
                    417: this.retryPolling.bind(this)
                },
                success: this.redirectToSuccess.bind(this),
                error: this.redirectToError.bind(this)
            });
        },

        redirectToSuccess() {
            window.location = this.opts.successUrl;
        },

        redirectToError() {
            window.location = this.opts.errorUrl;
        },

        retryPolling() {
            setTimeout(this.poll, this.options.pollingInterval);
        }
    });

    window.StateManager.addPlugin('*[data-swagPayPalUnifiedPolling="true"]', 'swagPayPalUnifiedPolling');
})(jQuery, window);
