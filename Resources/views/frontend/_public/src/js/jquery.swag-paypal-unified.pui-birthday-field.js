(function($, window, document) {
    'use strict';

    $.plugin('swagPayPalUnifiedPayUponInvoiceBirthdayField', {
        defaults: {
            submitButtonSelector: '.swag-payment-paypal-unified-pay-upon-invoice-legal-text-container button[type="submit"]',

            dayFieldSelector: '[name="puiDateOfBirth[day]"]',

            monthFieldSelector: '[name="puiDateOfBirth[month]"]',

            yearFieldSelector: '[name="puiDateOfBirth[year]"]',

            singleDateFieldSelector: 'input[type="text"]',

            wrapperDivSelector: '.field--select',

            hasErrorClass: 'has--error',

            isSingleField: false,

            offsetAdd: 30,

            scrollSpeed: 400
        },

        isFlatPickerField: true,

        init: function() {
            this.applyDataAttributes();

            this.$submitButton = $(this.opts.submitButtonSelector);

            if (!this.opts.isSingleField) {
                this.$dayField = $(this.opts.dayFieldSelector);
                this.$monthField = $(this.opts.monthFieldSelector);
                this.$yearField = $(this.opts.yearFieldSelector);
            } else {
                this.$singleDateField = this.$el.find(this.opts.singleDateFieldSelector);
                if (this.$singleDateField.length === 0) {
                    this.isFlatPickerField = false;
                    this.$singleDateField = this.$el.find(this.opts.singleDateFieldSelector.replace('text', 'date'));
                }
            }

            this.registerEvents();
        },

        registerEvents: function() {
            this.$submitButton.on('click', $.proxy(this.onSubmitButtonClick, this));

            if (!this.opts.isSingleField) {
                this.$dayField.on('change', $.proxy(this.onChange, this, { field: this.$dayField }));
                this.$monthField.on('change', $.proxy(this.onChange, this, { field: this.$monthField }));
                this.$yearField.on('change', $.proxy(this.onChange, this, { field: this.$yearField }));

                return;
            }

            if (!this.isFlatPickerField) {
                this.$singleDateField.on('change', $.proxy(this.onChange, this, { field: this.$singleDateField }));

                return;
            }

            $.subscribe(this.getEventName('plugin/swDatePicker/onInputChange'), $.proxy(this.onChange, this, { field: this.$singleDateField }));
        },

        onSubmitButtonClick: function() {
            if (this.opts.isSingleField) {
                this.handleSingleField();

                return;
            }

            this.handleMultiField();
        },

        onChange: function(field) {
            field.field.removeClass(this.opts.hasErrorClass);
        },

        handleMultiField: function() {
            var dayFieldValue = this.$dayField.val(),
                monthFieldValue = this.$monthField.val(),
                yearFieldValue = this.$yearField.val();

            if (dayFieldValue && monthFieldValue && yearFieldValue) {
                return;
            }

            if (!dayFieldValue) {
                this.$dayField.addClass(this.opts.hasErrorClass);
            }

            if (!monthFieldValue) {
                this.$monthField.addClass(this.opts.hasErrorClass);
            }

            if (!yearFieldValue) {
                this.$yearField.addClass(this.opts.hasErrorClass);
            }

            this.scrollToElement(this.$dayField);
        },

        handleSingleField: function() {
            var value = this.$singleDateField.val();

            if (!value) {
                this.$singleDateField.addClass(this.opts.hasErrorClass);

                this.scrollToElement(this.$singleDateField);
            }
        },

        scrollToElement: function($element) {
            $([document.documentElement, document.body]).animate({
                scrollTop: $element.offset().top - this.opts.offsetAdd
            }, this.opts.scrollSpeed);
        }
    });

    window.StateManager.addPlugin('*[data-swagPuiBirthdayField="true"]', 'swagPayPalUnifiedPayUponInvoiceBirthdayField');
})(jQuery, window, document);
