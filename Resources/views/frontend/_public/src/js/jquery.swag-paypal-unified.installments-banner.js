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
            textColor: 'black'
        },

        init: function() {
            this.payPalInstallmentsBannerJS = window.payPalInstallmentsBannerJS;
            if (this.payPalInstallmentsBannerJS === undefined) {
                return;
            }

            this.checkGetCookiePreference();
            if (this.isPayPalAllowed() === false) {
                return;
            }

            this.applyDataAttributes();
            $.publish('plugin/swagPayPalUnifiedInstallmentsBanner/init', this);

            this.createBanner();

            $.publish('plugin/swagPayPalUnifiedInstallmentsBanner/bannerCreated', this);
        },

        createBanner: function() {
            this.payPalInstallmentsBannerJS.Messages({
                amount: this.opts.amount,
                currency: this.opts.currency,
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

        checkGetCookiePreference: function() {
            if ($.isFunction($.getCookiePreference)) {
                return;
            }

            // Polyfill for older shopware versions
            $.getCookiePreference = function() {
                return true;
            };
        },

        isPayPalAllowed: function() {
            var me = this;

            me.cookieValue = me.getCookie();

            return me.cookieValue || $.getCookiePreference('paypal-cookies');
        },

        getCookie: function() {
            var name = "allowCookie=",
                decodedCookie = decodeURIComponent(document.cookie),
                cookieArray = decodedCookie.split(';');

            for (var i = 0; i < cookieArray.length; i++) {
                var cookie = cookieArray[i];
                while (cookie.charAt(0) === ' ') {
                    cookie = cookie.substring(1);
                }
                if (cookie.indexOf(name) === 0) {
                    return cookie.substring(name.length, cookie.length);
                }
            }

            return null;
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
