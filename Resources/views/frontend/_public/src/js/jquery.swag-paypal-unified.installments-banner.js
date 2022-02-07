;(function($, window, document, undefined) {
    'use strict';

    $.plugin('swagPayPalUnifiedInstallmentsBanner', {
        defaults: {
            /**
             * Amount of money, which will be used to calculate the examples
             *
             * @type number
             */
            amount: 0,

            /**
             * Currency used for the examples
             *
             * @type string
             */
            currency: 'EUR',

            /**
             * Layout of the installments banner
             * Available layouts:
             *  - flex (graphical)
             *  - text
             *
             * @type string
             */
            layout: 'flex',

            /**
             * Color of the graphical banner
             * Available colors:
             *  - blue
             *  - black
             *  - white
             *  - gray
             *
             * @type string
             */
            color: 'blue',

            /**
             * Ratio of the graphical banner
             * Available values:
             *  - 1x1
             *  - 20x1
             *  - 8x1
             *  - 1x4
             *
             * @type string
             */
            ratio: '8x1',

            /**
             * Layout type for the text banner
             * Available values:
             *  - primary
             *  - alternative
             *  - inline
             *  - none
             *
             * @type string
             */
            logoType: 'primary',

            /**
             * Text color of the text banner.
             * Available values:
             *  - black
             *  - white
             */
            textColor: 'black',

            /**
             * Buyer country used to get the right banner language
             *
             * @type string|null
             */
            buyerCountry: null
        },

        init: function() {
            this.applyDataAttributes();

            this.payPalInstallmentsBannerJS = window.payPalInstallmentsBannerJS;

            if (this.payPalInstallmentsBannerJS === undefined || !this.opts.buyerCountry) {
                return;
            }

            $.publish('plugin/swagPayPalUnifiedInstallmentsBanner/init', this);

            this.createBanner();

            $.publish('plugin/swagPayPalUnifiedInstallmentsBanner/bannerCreated', this);
        },

        createBanner: function() {
            this.payPalInstallmentsBannerJS.Messages({
                amount: this.opts.amount,
                currency: this.opts.currency,
                buyerCountry: this.opts.buyerCountry,
                style: {
                    layout: this.opts.layout,
                    color: this.opts.color,
                    ratio: this.opts.ratio,
                    logo: {
                        type: this.opts.logoType
                    },
                    text: {
                        color: this.opts.textColor
                    }
                }
            }).render(this.$el.get(0));
        },

        destroy: function() {
            this._destroy();
        }
    });

    $.subscribe('plugin/swAjaxVariant/onRequestData', function() {
        window.StateManager.addPlugin('*[data-paypalUnifiedInstallmentsBanner="true"]', 'swagPayPalUnifiedInstallmentsBanner');
    });

    window.StateManager.addPlugin('*[data-paypalUnifiedInstallmentsBanner="true"]', 'swagPayPalUnifiedInstallmentsBanner');
})(jQuery, window, document);
