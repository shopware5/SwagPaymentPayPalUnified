;(function($) {
    'use strict';

    $.plugin('swagPayPalUnifiedFraudNet', {
        defaults: {
            fraudNetUrl: 'https://c.paypal.com/da/r/fb.js',

            fraudNetClass: 'fnparams-dede7cc5-15fd-4c75-a9f4-36c430ee3a99',

            /**
             * Change this to a 32char guid/uuid.
             */
            fraudNetSessionId: null,

            /**
             * The flow id provided to you. Unique for each web page
             */
            fraudNetFlowId: null
        },

        body: null,

        init: function() {
            this.body = $('body');

            this.addConfigScript();
            this.addFraudNetScript();
        },

        addConfigScript: function() {
            var config = {
                    f: this.opts.fraudNetSessionId,
                    s: this.opts.fraudNetFlowId
                },

                scriptTag = $('<script/>')
                    .attr('type', 'application/json')
                    .attr('fncls', this.opts.fraudNetClass)
                    .html(JSON.stringify(config));

            this.body.append(scriptTag);
        },

        addFraudNetScript: function() {
            var scriptTag = $('<script/>')
                .attr('src', this.opts.fraudNetUrl);

            this.body.append(scriptTag);
        }
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedFraudNet="true"]', 'swagPayPalUnifiedFraudNet');
})(jQuery);
