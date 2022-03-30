;(function($) {
    'use strict';

    $.plugin('swagPayPalUnifiedFraudNet', {
        defaults: {
            /**
             * @type string
             */
            fraudNetUrl: 'https://c.paypal.com/da/r/fb.js',

            /**
             * @type string
             */
            fraudNetClass: 'fnparams-dede7cc5-15fd-4c75-a9f4-36c430ee3a99',

            /**
             * Change this to a 32char guid/uuid.
             *
             * @type string
             */
            fraudNetSessionId: null,

            /**
             * The flow id provided to you. Unique for each web page
             *
             * @type string
             */
            fraudNetFlowId: null,

            /**
             * @type boolean
             */
            fraudnetSandbox: false
        },

        body: null,

        init: function() {
            this.body = $('body');

            this.applyDataAttributes();

            this.addConfigScript();
            this.addFraudNetScript();
        },

        addConfigScript: function() {
            var config = {
                    f: this.opts.fraudNetSessionId,
                    s: this.opts.fraudNetFlowId,
                    sandbox: this.opts.fraudnetSandbox
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
