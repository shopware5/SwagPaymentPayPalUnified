(function($, window) {
    'use strict';

    $.plugin('swagPayPalUnifiedPayUponInvoicePhoneNumberField', {
        defaults: {
            /**
             * @type RegExp
             */
            phoneNumberFieldCharRegex: /[0-9+-/() ]|backspace|control|shift|a|x|v|c|delete/i,

            /**
             * @type RegExp
             */
            phoneNumberFieldMatchRegEx: /^[0-9+-/() ]*$/g,

            /**
             * @type RegExp
             */
            phoneNumberFieldValueReplaceRegEx: /[0-9+-/() ]/g
        },

        init: function() {
            this.applyDataAttributes();

            this.$el.on('keydown blur input', this.filterInput.bind(this));
        },

        /**
         * @param event
         */
        filterInput: function(event) {
            if (event.type === 'input' || event.type === 'blur') {
                if (!this.$el.val().match(this.opts.phoneNumberFieldMatchRegEx)) {
                    var value = this.$el.val(),
                        newValue = '';

                    for (var i = 0; i < value.length; i++) {
                        if (value.charAt(i).match(this.opts.phoneNumberFieldValueReplaceRegEx)) {
                            newValue += value.charAt(i);
                        }
                    }

                    this.$el.val(newValue);
                }

                return;
            }

            if (event.type === 'keydown' && event.key) {
                if (!event.key.match(this.opts.phoneNumberFieldCharRegex)) {
                    event.preventDefault();
                }
            }
        }
    });

    window.StateManager.addPlugin('*[data-swagPuiTelephoneNumberField="true"]', 'swagPayPalUnifiedPayUponInvoicePhoneNumberField');
})(jQuery, window);
