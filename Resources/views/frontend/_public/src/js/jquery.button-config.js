(function($) {
    /**
     * @param { object } opts
     */
    $.swagPayPalCreateButtonSizeObject = function(opts) {
        return {
            small: {
                height: opts.smallHeight,
                widthClass: opts.smallWidthClass
            },

            medium: {
                height: opts.mediumHeight,
                widthClass: opts.mediumWidthClass
            },

            large: {
                height: opts.largeHeight,
                widthClass: opts.largeWidthClass
            },

            responsive: {
                height: opts.responsiveHeight,
                widthClass: opts.responsiveWidthClass
            }
        };
    };

    /**
     * @param { object } opts
     * @param { object } buttonSize
     * @param { boolean } applyColor
     */
    $.swagPayPalCreateButtonStyle = function(opts, buttonSize, applyColor) {
        var config = {
            label: opts.label,
            shape: opts.shape,
            layout: opts.layout,
            tagline: opts.tagline,
            height: buttonSize[opts.size].height
        };

        if (applyColor) {
            config.color = opts.color;
        }

        return config;
    };

    $.swagPayPalCreateDefaultPluginConfig = function() {
        return {
            /**
             * @type string
             */
            sdkUrl: 'https://www.paypal.com/sdk/js',

            /**
             * Holds the client id
             *
             * @type string
             */
            clientId: '',

            /**
             * Use PayPal debug mode
             *
             * @type boolean
             */
            useDebugMode: false,

            /**
             * Currency which should be used for the Smart Payment Buttons
             *
             * @type string
             */
            currency: 'EUR',

            /**
             * @type string
             */
            paypalIntent: 'capture',

            /**
             * The language ISO (ISO_639) or the Smart Payment Buttons.
             *
             * for possible values see: https://developer.paypal.com/api/rest/reference/locale-codes/
             *
             * @type string
             */
            locale: '',

            /**
             * label of the button
             * possible values:
             *  - buynow
             *  - checkout
             *  - credit
             *  - pay
             *
             * IMPORTANT: Changing this value can lead to legal issues!
             *
             * @type string
             */
            label: 'buynow',

            /**
             * color of the button
             * possible values:
             *  - gold
             *  - blue
             *  - silver
             *  - black
             *
             * @type string
             */
            color: 'gold',

            /**
             * size of the button
             * possible values:
             *  - small
             *  - medium
             *  - large
             *  - responsive
             *
             * @type string
             */
            size: 'large',

            /**
             * shape of the button
             * possible values:
             *  - pill
             *  - rect
             *
             * @type string
             */
            shape: 'rect',

            /**
             *  @type string
             */
            layout: 'horizontal',

            /**
             * show text under the button
             *
             * @type boolean
             */
            tagline: false,

            /**
             * PayPal button height small
             *
             * @type number
             */
            smallHeight: 25,

            /**
             * PayPal button height medium
             *
             * @type number
             */
            mediumHeight: 35,

            /**
             * PayPal button height large
             *
             * @type number
             */
            largeHeight: 45,

            /**
             * PayPal button height responsive
             *
             * @type number
             */
            responsiveHeight: 55,

            /**
             * PayPal button width small
             *
             * @type string
             */
            smallWidthClass: 'paypal-button-width--small',

            /**
             * PayPal button width medium
             *
             * @type string
             */
            mediumWidthClass: 'paypal-button-width--medium',

            /**
             * PayPal button width large
             *
             * @type string
             */
            largeWidthClass: 'paypal-button-width--large',

            /**
             * PayPal button width responsive
             *
             * @type string
             */
            responsiveWidthClass: 'paypal-button-width--responsive',

            /**
             * selector for the checkout confirm form element
             *
             * @type string
             */
            confirmFormSelector: '#confirm--form',

            /**
             * selector for the submit button of the checkout confirm form
             *
             * @type string
             */
            confirmFormSubmitButtonSelector: ':submit[form="confirm--form"]',

            /**
             * The class name to identify whether the PayPal sdk has been loaded
             *
             * @type string
             */
            paypalScriptLoadedSelector: 'paypal-checkout-js-loaded',

            /**
             *  @type string
             */
            hiddenClass: 'is--hidden',

            /**
             * The URL used to create the order
             *
             * @type string
             */
            createOrderUrl: '',

            /**
             * After approval, redirect to this URL
             *
             * @type string
             */
            returnUrl: '',

            /**
             * This page will be opened when the payment creation fails.
             *
             * @type string
             */
            paypalErrorPage: ''
        };
    };
})(jQuery);
