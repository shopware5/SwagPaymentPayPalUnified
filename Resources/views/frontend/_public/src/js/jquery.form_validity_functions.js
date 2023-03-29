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
    SwagPaymentPaypalFormBaseFunction.prototype.radioType = 'radio';

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
            if (me.checkFormValidity(false)) {
                me.clearErrorClass();
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
                this.radioType,
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
                if ($element.attr('type') === me.radioType) {
                    isValid = me.validateRadioInputs($element);
                } else {
                    isValid = false;
                }

                if (!isInitial && !isValid) {
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

    /**
     * @param { object }$element
     * @returns { boolean }
     */
    SwagPaymentPaypalFormBaseFunction.prototype.validateRadioInputs = function($element) {
        var checkedRadio = this.$form.find("input[type='radio'][name='%s']:checked".replace('%s', $element.attr('name')));

        return checkedRadio.length > 0;
    };

    /**
     * @param { object }$element
     */
    SwagPaymentPaypalFormBaseFunction.prototype.addErrorClass = function($element) {
        var isRadio = $element.attr('type') === this.radioType,
            elementName = isRadio ? $element.attr('id') : $element.attr('name'),
            $label = $('label[for="%s"]'.replace('%s', elementName));

        $label.addClass(this.hasErrorClass);
    };

    SwagPaymentPaypalFormBaseFunction.prototype.clearErrorClass = function () {
        this.$form.find('label').removeClass(this.hasErrorClass);
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
