(function($) {
    /**
     * @param { string } formSelector
     * @param { string } submitButtonSelector
     * @param { string } hiddenClass
     * @param { string } eventDomain
     *
     * @constructor
     */
    var SwagPaymentPaypalFormBaseFunction = function(formSelector, submitButtonSelector, hiddenClass, eventDomain) {
        this.formSelector = formSelector;
        this.submitButtonSelector = submitButtonSelector;
        this.hiddenClass = hiddenClass;
        this.eventDomain = eventDomain;

        this.$form = $(this.formSelector);
        this.$submitButton = $(this.submitButtonSelector);
    };

    SwagPaymentPaypalFormBaseFunction.prototype.hasErrorClass = 'has--error';

    SwagPaymentPaypalFormBaseFunction.prototype.hideConfirmButton = function() {
        this.$submitButton.addClass(this.hiddenClass);

        var eventName = ['plugin', this.eventDomain, 'hideConfirmButton'].join('/');

        $.publish(eventName, [this, this.$submitButton]);
    };

    SwagPaymentPaypalFormBaseFunction.prototype.disableConfirmButton = function() {
        this.$form.on('submit', $.proxy(this.onConfirmCheckout, this));
    };

    /**
     * @param { Event } event
     */
    SwagPaymentPaypalFormBaseFunction.prototype.onConfirmCheckout = function(event) {
        event.preventDefault();
    };

    SwagPaymentPaypalFormBaseFunction.prototype.onPayPalButtonClick = function() {
        if (this.checkFormValidity()) {
            return;
        }

        this.$submitButton.trigger('click');
    };

    /**
     * @param { Object } data
     * @param { Object } actions
     */
    SwagPaymentPaypalFormBaseFunction.prototype.onInitPayPalButton = function(data, actions) {
        var me = this;

        if (!this.checkFormValidity(true)) {
            actions.disable();
        }

        this.$form.on('change', function() {
            if (me.checkFormValidity()) {
                actions.enable();
                return;
            }

            actions.disable();
        });
    };

    /**
     * @param { boolean } isInitial
     *
     * @returns { boolean }
     */
    SwagPaymentPaypalFormBaseFunction.prototype.checkFormValidity = function(isInitial) {
        var me = this,
            isValid = true,
            checkedAttributeTypes = [
                'radio',
                'checkbox'
            ];

        if (Object.prototype.hasOwnProperty.call(this.$form.get(0), 'checkValidity')) {
            return this.$form.get(0).checkValidity();
        }

        this.$form.find('select, textarea, input').each(function() {
            var $element = $(this);

            if (!$element.prop('required')) {
                return;
            }

            if (checkedAttributeTypes.indexOf($element.attr('type')) >= 0 && !$element.is(':checked')) {
                isValid = false;
                if (!isInitial) {
                    me.addErrorClass($element);
                }

                return;
            }

            if (!$element.val()) {
                isValid = false;
                if (!isInitial) {
                    me.addErrorClass($element);
                }
            }
        });

        return isValid;
    };

    SwagPaymentPaypalFormBaseFunction.prototype.addErrorClass = function($element) {
        var elementName = $element.attr('name'),
            $lable = $('label[for="%s"]'.replace('%s', elementName));

        $lable.addClass(this.hasErrorClass);
    };

    /**
     * @param { string } formSelector
     * @param { string } submitButtonSelector
     * @param { string } hiddenClass
     * @param { string } eventDomain
     *
     * @returns { SwagPaymentPaypalFormBaseFunction }
     */
    $.createSwagPaymentPaypalFormValidityFunctions = function(formSelector, submitButtonSelector, hiddenClass, eventDomain) {
        return new SwagPaymentPaypalFormBaseFunction(formSelector, submitButtonSelector, hiddenClass, eventDomain);
    };
})(jQuery);
