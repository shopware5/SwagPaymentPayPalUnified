(function($) {
    var SwagPaymentPaypalCreateOrderFunction = function(createOrderUrl, sourcePlugin) {
        this.createOrderUrl = createOrderUrl;
        this.sourcePlugin = sourcePlugin;
    };

    SwagPaymentPaypalCreateOrderFunction.prototype.storageFieldPluginCssSelector = '*[data-storage-field="true"]';
    SwagPaymentPaypalCreateOrderFunction.prototype.storageFieldPluginSelector = 'plugin_swStorageField';

    SwagPaymentPaypalCreateOrderFunction.prototype.customerCommentFieldSelector = '.user-comment--field';
    SwagPaymentPaypalCreateOrderFunction.prototype.fallbackParamerterName = 'sComment';

    SwagPaymentPaypalCreateOrderFunction.prototype.registerNewsletterFieldSelector = '#sNewsletter';
    SwagPaymentPaypalCreateOrderFunction.prototype.registerNewsletterParamerterName = 'sNewsletter';

    SwagPaymentPaypalCreateOrderFunction.prototype.createOrder = function() {
        var me = this;

        return $.ajax({
            method: 'post',
            url: this.createOrderUrl,
            data: this.createExtraData()
        }).then(function(response) {
            me.sourcePlugin.opts.basketId = response.basketId;

            return response.token;
        }, function(response) {
            me.latestResponse = response;
        }).promise();
    };

    SwagPaymentPaypalCreateOrderFunction.prototype.onApiError = function() {
        if (!this.latestResponse) {
            this._redirectToErrorPageIfAvailable();
            return;
        }

        if (this.latestResponse.responseText !== '') {
            var jsonResponse = JSON.parse(this.latestResponse.responseText);

            delete this.latestResponse;

            if (jsonResponse.redirectTo) {
                $.redirectToUrl(jsonResponse.redirectTo);
                return;
            }
        }

        this._redirectToErrorPageIfAvailable();
    };

    SwagPaymentPaypalCreateOrderFunction.prototype.createExtraData = function() {
        var me = this,
            $formElements = $(this.storageFieldPluginCssSelector),
            result = this.checkNewsletterCheckbox();

        // This is a fallback for Shopware versions that do not have swStorageField jQuery plugin.
        if (!$.isFunction($.fn.swStorageField)) {
            return this.createExtraDataLegacy(result);
        }

        $formElements.each(function(index, formElement) {
            var $formElement = $(formElement),
                $formElementPlugin = $formElement.data(me.storageFieldPluginSelector),
                storageKeyName = $formElementPlugin.storageKey.replace($formElementPlugin.opts.storageKeyPrefix, ''),
                storageItemValue;

            if (!$formElementPlugin || !$formElementPlugin.$el.length) {
                return;
            }

            if (storageKeyName) {
                storageItemValue = $formElementPlugin.storage.getItem($formElementPlugin.getStorageKey());
            }

            if (storageItemValue) {
                result[storageKeyName] = storageItemValue;
            }

            $formElementPlugin.onFormSubmit();
        });

        return result;
    };

    SwagPaymentPaypalCreateOrderFunction.prototype.createExtraDataLegacy = function(result) {
        var me = this,
            $formElements = $(':input');

        $formElements.each(function(index, formElement) {
            var $formElement = $(formElement),
                storageKeyName = $formElement.attr('name');

            if ($formElement.hasClass(me.customerCommentFieldSelector)) {
                storageKeyName = me.fallbackParamerterName;
            }

            if (!storageKeyName) {
                return;
            }

            result[storageKeyName.toLowerCase()] = $formElement.val();
        });

        return result;
    };

    SwagPaymentPaypalCreateOrderFunction.prototype.checkNewsletterCheckbox = function() {
        var $registerNewsletterField = $(this.registerNewsletterFieldSelector),
            result = {};

        if ($registerNewsletterField.length && $registerNewsletterField.is(':checked')) {
            result[this.registerNewsletterParamerterName] = true;
        }

        return result;
    };

    SwagPaymentPaypalCreateOrderFunction.prototype._redirectToErrorPageIfAvailable = function() {
        if (!this.sourcePlugin.opts.paypalErrorPage) {
            return;
        }

        $.redirectToUrl(this.sourcePlugin.opts.paypalErrorPage);
    };

    $.createSwagPaymentPaypalCreateOrderFunction = function(createOrderUrl, sourcePlugin) {
        return new SwagPaymentPaypalCreateOrderFunction(createOrderUrl, sourcePlugin);
    };
})(jQuery);
